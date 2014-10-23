<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Exception\UnknownProcedureException;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\RegistryInterface;
use Icecave\Overpass\Rpc\RpcServerInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpRpcServer implements RpcServerInterface
{
    /**
     * @param RegistryInterface           $registry
     * @param AMQPChannel                 $channel
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     */
    public function __construct(
        RegistryInterface $registry,
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        SerializationInterface $serialization = null
    ) {
        $this->registry = $registry;
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JsonSerialization();
        $this->consumerTags = [];
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
            return;
        }

        $handler = function ($message) {
            $this->recv($message);
        };

        foreach ($this->registry->procedures() as $procedureName) {
            $this->consumerTags[$procedureName] = $this->channel->basic_consume(
                $this->declarationManager->requestQueue($procedureName),
                '',    // consumer tag
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                $handler
            );
        }

        while ($this->channel->callbacks) {
            $this->channel->wait();
        }

        $this->stop();
    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {
        $consumerTags = $this->consumerTags;

        foreach ($consumerTags as $procedureName => $consumerTag) {
            $this->channel->basic_cancel($consumerTag);
            unset($this->consumerTags[$procedureName]);
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
        $this->channel->basic_publish(
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
        try {
            $payload = $this
                ->serialization
                ->unserialize($message->body);

            $request = Request::createFromPayload($payload);

            $procedure = $this->registry->get($request->name());

            // Commit to handle this request. The acknowledgement must be sent
            // *before* the procedure is called, otherwise a procedure that
            // fails midway through may be retried and we cannot guarantee that
            // such behaviour is safe for all exposed procedures ...
            $this->channel->basic_ack(
                $message->get('delivery_tag')
            );

            $response = Response::create(
                $procedure->invoke($request->arguments())
            );

        // This could occur if the procedure is unregistered after a client has
        // already enqueued a request. The request is requeued as it may be
        // served by a different RPC server ...
        } catch (UnknownProcedureException $e) {
            $this->channel->basic_reject(
                $message->get('delivery_tag'),
                true
            );

            return;
        } catch (Exception $e) {
            $response = Response::createFromException($e);
        }

        $this->send($message, $response);
    }

    private $registry;
    private $channel;
    private $declarationManager;
    private $serialization;
    private $consumerTags;
}
