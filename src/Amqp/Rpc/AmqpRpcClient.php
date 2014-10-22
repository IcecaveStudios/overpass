<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Exception;
use Icecave\Overpass\Rpc\RpcClientInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpRpcClient implements RpcClientInterface
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
        $this->correlationId = 0;
    }

    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function call($name, array $arguments)
    {
        $this->initialize();

        $this->channel->basic_publish(
            $this->createMessage($name, $arguments),
            '', // default direct exchange
            $this->declarationManager->requestQueue($name)
        );

        $this->response = null;

        while (
            null === $this->response
            && $this->channel->callbacks
        ) {
            $this->channel->wait();
        }

        $response = $this->response;
        $this->response = null;

        if (count($response) === 1) {
            return $response[0];
        }

        list($code, $reason) = $response;

        // TODO throw proper exception type ...
        throw new Exception($reason, $code);
    }

    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function __call($name, array $arguments)
    {
        return $this->call($name, $arguments);
    }

    private function initialize()
    {
        if ($this->consumerTag) {
            return;
        }

        $this->consumerTag = $this->channel->basic_consume(
            $this->declarationManager->responseQueue(),
            '',    // consumer tag
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            function ($message) {
                $this->dispatch($message);
            }
        );
    }

    private function dispatch(AMQPMessage $message)
    {
        $correlationId = $message->get('correlation_id');

        if ($correlationId < $this->correlationId) {
            return;
        } elseif ($correlationId > $this->correlationId) {
            throw new RuntimeException('Response skipped.'); // TODO improve
        }

        $this->response = $this
            ->serialization
            ->unserialize($message->body);
    }

    /**
     * Create the RPC request message.
     *
     * @param string $procedureName
     * @param array  $arguments
     *
     * @return AMQPMessage
     */
    private function createMessage($procedureName, array $arguments)
    {
        $payload = $this
            ->serialization
            ->serialize($arguments);

        return new AMQPMessage(
            $payload,
            [
                'reply_to' => $this->declarationManager->responseQueue(),
                'correlation_id' => ++$this->correlationId,
            ]
        );
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $correlationId;
    private $consumerTag;
    private $response;
}
