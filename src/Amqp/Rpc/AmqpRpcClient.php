<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Overpass\Rpc\Message\MessageSerialization;
use Icecave\Overpass\Rpc\Message\MessageSerializationInterface;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\RpcClientInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class AmqpRpcClient implements RpcClientInterface
{
    use LoggerAwareTrait;

    /**
     * @param AMQPChannel                        $channel
     * @param DeclarationManager|null            $declarationManager
     * @param MessageSerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        MessageSerializationInterface $serialization = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new MessageSerialization(new JsonSerialization());
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

        $request = Request::create($name, $arguments);
        $correlationId = ++$this->correlationId;

        if ($this->logger) {
            $this->logger->debug(
                'RPC #{id} {request}',
                [
                    'id'      => $correlationId,
                    'request' => $request,
                ]
            );
        }

        $this->send($request);

        $response = $this->wait();

        if ($this->logger) {
            $this->logger->debug(
                'RPC #{id} {request} -> {response}',
                [
                    'id'       => $correlationId,
                    'request'  => $request,
                    'response' => $response,
                ]
            );
        }

        return $response->extract();
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

    /**
     * Initialize AMQP resources.
     */
    private function initialize()
    {
        if ($this->consumerTag) {
            return;
        }

        $queue = $this
            ->declarationManager
            ->responseQueue();

        $this->consumerTag = $this
            ->channel
            ->basic_consume(
                $queue,
                '',    // consumer tag
                false, // no local
                true,  // no ack
                true,  // exclusive
                false, // no wait
                function ($message) {
                    $this->recv($message);
                }
            );
    }

    /**
     * Send an RPC request.
     *
     * @param Request $request
     */
    private function send(Request $request)
    {
        $payload = $this
            ->serialization
            ->serializeRequest($request);

        $exchange = $this
            ->declarationManager
            ->exchange();

        $requestQueue = $this
            ->declarationManager
            ->requestQueue($request->name());

        $responseQueue = $this
            ->declarationManager
            ->responseQueue();

        $message = new AMQPMessage(
            $payload,
            [
                'reply_to'       => $responseQueue,
                'correlation_id' => $this->correlationId,
            ]
        );

        $this
            ->channel
            ->basic_publish(
                $message,
                $exchange,
                $request->name()
            );
    }

    /**
     * Receive an RPC response.
     *
     * @param AMQPMessage $message
     */
    private function recv(AMQPMessage $message)
    {
        $correlationId = $message->get('correlation_id');

        if ($correlationId < $this->correlationId) {
            return;
        } elseif ($correlationId > $this->correlationId) {
            throw new RuntimeException(
                'Out-of-order RPC response returned by server.'
            );
        }

        $this->response = $this
            ->serialization
            ->unserializeResponse($message->body);
    }

    /**
     * Wait for an RPC response.
     *
     * @return Response
     */
    private function wait()
    {
        while (
            !$this->response
            && $this->channel->callbacks
        ) {
            $this->channel->wait();
        }

        $response = $this->response;
        $this->response = null;

        return $response;
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $correlationId;
    private $consumerTag;
    private $response;
}
