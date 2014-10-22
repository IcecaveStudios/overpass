<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Exception;
use Icecave\Overpass\Rpc\RpcServerInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpRpcServer implements RpcServerInterface
{
    /**
     * @param AMQPChannel                 $channel
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        SerializationInterface $serialization = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JsonSerialization();
        $this->procedures = [];
    }

    /**
     * Register a procedure with the RPC server.
     *
     * @param string   $name      The name the under which the procedure is exposed.
     * @param callable $procedure The procedure to expose.
     */
    public function register($name, callable $procedure)
    {
        $this->procedures[$name] = (object) [
            'invoker' => new Procedure($procedure),
            'consumerTag' => null,
        ];
    }

    /**
     * Unregister a procedure with the RPC server.
     *
     * @param string $name The name the under which the procedure is exposed.
     */
    public function unregister($name)
    {
        unset($this->procedures[$name]);
    }

    /**
     * Check if the RPC server has a procedure registered under the given name.
     *
     * @param string $name The name the under which the procedure is exposed.
     *
     * @param boolean True if there is a procedure with the given name.
     */
    public function has($name)
    {
        return isset($this->procedures[$name]);
    }

    /**
     * Run the RPC server.
     */
    public function run()
    {
        if (!$this->procedures) {
            return;
        }

        foreach ($this->procedures as $procedureName => $procedure) {
            $procedure->consumerTag = $this->channel->basic_consume(
                $this->declarationManager->requestQueue($procedureName),
                '',
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                function ($message) use ($procedureName) {
                    $this->dispatch($procedureName, $message);
                }
            );
        }

        while ($this->channel->callbacks) {
            $this->channel->wait();
        }
    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {
        foreach ($this->procesures as $procedure) {
            if ($procedure->consumerTag) {
                $this->channel->basic_cancel($procedure->consumerTag);
                $procedure->consumerTag = null;
            }
        }
    }

    private function dispatch($procedureName, AMQPMessage $message)
    {
        // The procedure is no longer exposed, but may still be handled by
        // another RPC server, so reject the message and instruct the server to
        // re-queue the message ...
        if (!$this->has($procedureName)) {
            $this->channel->basic_reject(
                $message->get('delivery_tag'),
                true
            );

            return;
        }

        // Commit to handle this request. The acknowledgement must be sent
        // *before* the procedure is called, otherwise a procedure call that
        // fails midway through may be retried and we cannot guarantee that such
        // behaviour is safe for all exposed procedures ...
        $this->channel->basic_ack(
            $message->get('delivery_tag')
        );

        try {
            // Unserialize the arguments ...
            $arguments = $this
                ->serialization
                ->unserialize($message->body);

            // Attempt to invoke the procedure. Successful responses are encoded
            // as 1-tuple containing the result ...
            $response = [
                $this
                    ->procedures[$procedureName]
                    ->invoker
                    ->invoke($arguments)
            ];
        } catch (Exception $e) {
            // Failure responses are encoded as a 2-tuple containing the error
            // code and exception message ...
            $response = [
                $e->getCode(),
                $e->getMessage(),
            ];
        }

        $this->respond($message, $response);
    }

    /**
     * Send a payload in response to a previously received message.
     *
     * @param AMQPMessage $message
     * @param mixed       $payload
     */
    private function respond(AMQPMessage $message, $payload)
    {
        // The message did not supply a reply queue, and is therefore
        // uninterested in the result ...
        if (!$message->has('reply_to')) {
            return;
        }

        // Serialize the response payload ...
        $payload = $this
            ->serialization
            ->serialize($payload);

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

    private $channel;
    private $declarationManager;
    private $serialization;
    private $procedures;
}
