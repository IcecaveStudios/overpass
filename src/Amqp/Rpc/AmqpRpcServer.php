<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Exception;
use Icecave\Repr\Repr;
use Icecave\Isolator\IsolatorTrait;
use Icecave\Overpass\Amqp\ChannelDispatcher;
use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Rpc\Invoker;
use Icecave\Overpass\Rpc\InvokerInterface;
use Icecave\Overpass\Rpc\Message\MessageSerialization;
use Icecave\Overpass\Rpc\Message\MessageSerializationInterface;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\Message\ResponseCode;
use Icecave\Overpass\Rpc\RpcServerInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LogLevel;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class AmqpRpcServer implements RpcServerInterface
{
    use IsolatorTrait;
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface                    $logger
     * @param AMQPChannel                        $channel
     * @param DeclarationManager|null            $declarationManager
     * @param MessageSerializationInterface|null $serialization
     * @param InvokerInterface|null              $invoker
     * @param ChannelDispatcher                  $channelDispatcher
     */
    public function __construct(
        LoggerInterface $logger,
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        MessageSerializationInterface $serialization = null,
        InvokerInterface $invoker = null,
        ChannelDispatcher $channelDispatcher = null
    ) {
        $this->channel            = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization      = $serialization ?: new MessageSerialization(new JsonSerialization);
        $this->invoker            = $invoker ?: new Invoker;
        $this->channelDispatcher  = $channelDispatcher ?: new ChannelDispatcher;
        $this->procedures         = [];
        $this->consumerTags       = [];

        $this->setLogger($logger);
    }

    /**
     * Expose a procedure.
     *
     * @param string   $name      The public name of the procedure.
     * @param callable $procedure The procedure implementation.
     *
     * @throws LogicException if the server is already running.
     */
    public function expose($name, callable $procedure)
    {
        if ($this->channel->callbacks) {
            throw new LogicException(
                'Procedures can not be exposed while the server is running.'
            );
        }

        $this->procedures[$name] = $procedure;
    }

    /**
     * Expose all public methods on an object.
     *
     * @param object $object The object with the methods to expose.
     * @param string $prefix A string to prefix to all method names.
     */
    public function exposeObject($object, $prefix = '')
    {
        $reflector = new ReflectionClass($object);

        foreach ($reflector->getMethods() as $method) {
            if ($method->isStatic()) {
                continue;
            } elseif (!$method->isPublic()) {
                continue;
            } elseif ('_' === $method->getName()[0]) {
                continue;
            }

            $name = $prefix . $method->getName();

            $this->expose(
                $name,
                [$object, $method->getName()]
            );
        }
    }

    /**
     * Run the RPC server.
     */
    public function run()
    {
        $this->isStopping = false;

        if ($this->procedures) {
            $this->channel->basic_qos(
                0,    // unlimited pre-fetch size
                1,    // pre-fetch count of 1
                true  // pre-fetch count shared across all consumers on channel (https://www.rabbitmq.com/consumer-prefetch.html)
            );

            // Bind queues / consumers ...
            foreach ($this->procedures as $procedureName => $procedure) {
                $this->bind($procedureName);

                $this->logger->debug(
                    'rpc.server exposed procedure "{procedure}"',
                    ['procedure' => $procedureName]
                );
            }

            $this->logger->info('rpc.server started successfully');
        } else {
            $this->logger->warning('rpc.server started without exposed procedures');
        }

        while ($this->channel->callbacks) {
            $this->channelDispatcher->wait($this->channel);

            if ($this->isStopping) {
                foreach ($this->procedures as $procedureName => $procedure) {
                    $this->unbind($procedureName);
                }
            }
        }

        if ($this->uncaughtException !== null) {
            $this->logger->critical('rpc.server shutdown due to uncaught exception');
            throw $this->uncaughtException;
        }

        $this->logger->info('rpc.server shutdown gracefully');
    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {
        if (!$this->isStopping) {
            $this->isStopping = true;

            $this->logger->info('rpc.server stopping');
        }
    }

    /**
     * Send an RPC response as a reply to a previously received request.
     *
     * @param AMQPMessage $message
     * @param mixed       $payload
     */
    private function send(AMQPMessage $message, Response $response)
    {
        // The client did not supply a reply queue, and is therefore
        // uninterested in the result ...
        if (!$message->has('reply_to')) {
            return;
        }

        // Serialize the response payload ...
        $payload = $this
            ->serialization
            ->serializeResponse($response);

        // Include the correlation ID in the response if one was provided ...
        $properties = [];
        if ($message->has('correlation_id')) {
            $properties['correlation_id'] = $message->get('correlation_id');
        }

        // Send the response ...
        $this
            ->channel
            ->basic_publish(
                new AMQPMessage($payload, $properties),
                '', // default direct exchange
                $message->get('reply_to')
            );
    }

    /**
     * Receive an RPC request.
     *
     * @param AMQPMessage $message
     */
    private function recv(AMQPMessage $message)
    {
        $logLevel   = LogLevel::DEBUG;
        $logContext = [
            'id'        => '?',
            'queue'     => '-',
            'procedure' => '<unknown>',
            'arguments' => '<unknown>',
        ];

        if ($message->has('correlation_id')) {
            $logContext['id'] = $message->get('correlation_id');
        }

        if ($message->has('reply_to')) {
            $logContext['queue'] = $message->get('reply_to');
        }

        // Commit to handle this request. The acknowledgement must be sent
        // *before* the procedure is called, otherwise a procedure that
        // fails midway through may be retried and we cannot guarantee that
        // such behaviour is safe for all exposed procedures ...
        $this->channel->basic_ack(
            $message->get('delivery_tag')
        );

        try {
            $request = $this
                ->serialization
                ->unserializeRequest($message->body);

            $logContext['procedure'] = $request->name();
            $logContext['arguments'] = implode(
                ', ',
                array_map(
                    [Repr::class, 'repr'],
                    $request->arguments()
                )
            );

            $response = $this
                ->invoker
                ->invoke(
                    $request,
                    $this->procedures[$request->name()]
                );
        } catch (InvalidMessageException $e) {
            $logLevel = LogLevel::WARNING;
            $response = Response::createFromException($e);
        } catch (Exception $e) {
            $logLevel                = LogLevel::ERROR;
            $logContext['exception'] = $e;
            $response                = Response::create(
                ResponseCode::EXCEPTION(),
                'Internal server error.'
            );

            $this->isStopping = true;
            $this->uncaughtException = $e;
        }

        $this->send($message, $response);

        $logContext['code']  = $response->code();
        $logContext['value'] = Repr::repr($response->value());

        if (ResponseCode::SUCCESS() === $response->code()) {
            $logMessage = 'rpc.server {queue} #{id} {procedure}({arguments}) -> {value}';
        } else {
            $logMessage = 'rpc.server {queue} #{id} {procedure}({arguments}) -> {code} {value}';
        }

        $this->logger->log($logLevel, $logMessage, $logContext);
    }

    private function bind($procedureName)
    {
        $queue = $this
            ->declarationManager
            ->requestQueue($procedureName);

        $this->consumerTags[$procedureName] = $this
            ->channel
            ->basic_consume(
                $queue,
                '',    // consumer tag
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                function ($message) {
                    $this->recv($message);
                }
            );
    }

    private function unbind($procedureName)
    {
        $this
            ->channel
            ->basic_cancel(
                $this->consumerTags[$procedureName]
            );

        unset($this->consumerTags[$procedureName]);
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $invoker;
    private $channelDispatcher;
    private $isStopping;
    private $uncaughtException;
    private $procedures;
    private $consumerTags;
}
