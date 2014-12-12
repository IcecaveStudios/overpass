<?php
namespace Icecave\Overpass\Amqp\Rpc;

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
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use ReflectionClass;

class AmqpRpcServer implements RpcServerInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface                    $logger
     * @param AMQPChannel                        $channel
     * @param DeclarationManager|null            $declarationManager
     * @param MessageSerializationInterface|null $serialization
     * @param InvokerInterface|null              $invoker
     */
    public function __construct(
        LoggerInterface $logger,
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        MessageSerializationInterface $serialization = null,
        InvokerInterface $invoker = null
    ) {
        $this->channel            = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization      = $serialization ?: new MessageSerialization(new JsonSerialization);
        $this->invoker            = $invoker ?: new Invoker;
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
        $this->logger->info(
            'RPC server starting'
        );

        // Bind queues / consumers ...
        foreach ($this->procedures as $procedureName => $procedure) {
            $this->bind($procedureName);
        }

        $this->logger->info(
            'RPC server started successfully (procedures: {procedures})',
            [
                'procedures' => implode(
                    ', ',
                    array_keys($this->procedures)
                ) ?: '<none>'
            ]
        );

        // Wait for the server to be stopped ...
        while ($this->channel->callbacks) {
            // var_dump($this->channel->callbacks);
            $this->channel->wait();
        }

        $this->logger->info(
            'RPC server shutdown gracefully'
        );
    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {
        if ($this->channel->callbacks) {
            $this->logger->info(
                'RPC server stopping'
            );

            foreach (array_keys($this->consumerTags) as $procedureName) {
                $this->unbind($procedureName);
            }
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
        if ($message->has('correlation_id')) {
            $correlationId = $message->get('correlation_id');
        } else {
            $correlationId = '???';
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

            $procedureName = $request->name();

            $this->logger->debug(
                'RPC #{id} Request {request}',
                [
                    'id'      => $correlationId,
                    'request' => $request,
                ]
            );

            $response = $this
                ->invoker
                ->invoke(
                    $request,
                    $this->procedures[$request->name()]
                );
        } catch (InvalidMessageException $e) {
            $procedureName = '<invalid-request>';
            $response = Response::createFromException($e);
        }

        $this->send($message, $response);

        if (ResponseCode::SUCCESS() === $response->code()) {
            $infoValue = 'OK';
        } else {
            $infoValue = $response->value();
        }

        $this->logger->info(
            'RPC #{id} {procedure} {code} {value}',
            [
                'id'          => $correlationId,
                'procedure'   => $procedureName,
                'code'        => $response->code()->value(),
                'value'       => $infoValue,
            ]
        );

        $this->logger->debug(
            'RPC #{id} Response {response}',
            [
                'id'       => $correlationId,
                'response' => $response,
            ]
        );
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
                $handler = function ($message) {
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
    private $procedures;
    private $consumerTags;
}
