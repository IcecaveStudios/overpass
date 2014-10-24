<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Invoker;
use Icecave\Overpass\Rpc\InvokerInterface;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\RegistryInterface;
use Icecave\Overpass\Rpc\RpcServerInterface;
use Icecave\Overpass\Serialization\Exception\SerializationException;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class AmqpRpcServer implements RpcServerInterface
{
    use LoggerAwareTrait;

    /**
     * @param RegistryInterface           $registry
     * @param LoggerInterface             $logger
     * @param AMQPChannel                 $channel
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     * @param InvokerInterface|null       $invoker
     */
    public function __construct(
        RegistryInterface $registry,
        LoggerInterface $logger,
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        SerializationInterface $serialization = null,
        InvokerInterface $invoker = null
    ) {
        $this->registry = $registry;
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JsonSerialization();
        $this->invoker = $invoker ?: new Invoker();
        $this->isRunning = false;
        $this->consumerTags = [];

        $this->setLogger($logger);
    }

    /**
     * Get the registry used by this server to resolve procedure names.
     *
     * @return RegistryInterface The procedure registry.
     */
    public function registry()
    {
        return $this->registry;
    }

    /**
     * Run the RPC server.
     */
    public function run()
    {
        if ($this->registry->isEmpty()) {
            $this->logger->warning(
                'Cannot start RPC server - no procedures have been registered'
            );

            return;
        }

        $this->logger->info('RPC server starting');

        $procedures = $this->registry->procedures();

        foreach ($procedures as $procedureName) {
            $this->bind($procedureName);
        }

        $this->isRunning = true;
        $this->logger->info('RPC server started successfully');

        while (
            $this->isRunning
            && $this->channel->callbacks
        ) {
            $this->channel->wait();
        }

        foreach ($procedures as $procedureName) {
            $this->unbind($procedureName);
        }

        $this->logger->info('RPC server shutdown gracefully');
    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {
        if ($this->isRunning) {
            $this->isRunning = false;
            $this->logger->info('RPC server stopped');
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
            ->serialize($response);

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

        try {
            $payload = $this
                ->serialization
                ->unserialize($message->body);

            $request = Request::createFromPayload($payload);

            $this->logger->info(
                'RPC #{id} {request}',
                [
                    'id' => $correlationId,
                    'request' => $request,
                ]
            );

            $procedure = $this->registry->get(
                $request->name()
            );

            // Commit to handle this request. The acknowledgement must be sent
            // *before* the procedure is called, otherwise a procedure that
            // fails midway through may be retried and we cannot guarantee that
            // such behaviour is safe for all exposed procedures ...
            $this->channel->basic_ack(
                $message->get('delivery_tag')
            );

            $response = $this->invoker->invoke(
                $procedure,
                $request
            );

        } catch (SerializationException $e) {
            $response = Response::createFromException($e);
        } catch (RpcExceptionInterface $e) {
            $response = Response::createFromException($e);
        }

        $this->send($message, $response);

        $this->logger->info(
            'RPC #{id} {request} -> {response}',
            [
                'id' => $correlationId,
                'request' => $request,
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

        $this->logger->info(
            'Accepting requests for procedure: {procedure}',
            ['procedure' => $procedureName]
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

    private $registry;
    private $channel;
    private $declarationManager;
    private $serialization;
    private $invoker;
    private $isRunning;
    private $consumerTags;
}
