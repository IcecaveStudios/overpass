<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Isolator\IsolatorTrait;
use Icecave\Overpass\Rpc\Exception\TimeoutException;
use Icecave\Overpass\Rpc\Message\MessageSerialization;
use Icecave\Overpass\Rpc\Message\MessageSerializationInterface;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\RpcClientInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;

class AmqpRpcClient implements RpcClientInterface
{
    use IsolatorTrait;
    use LoggerAwareTrait;

    /**
     * @param AMQPChannel                        $channel
     * @param integer                            $timeout
     * @param DeclarationManager|null            $declarationManager
     * @param MessageSerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        $timeout = 10,
        DeclarationManager $declarationManager = null,
        MessageSerializationInterface $serialization = null
    ) {
        $this->channel            = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization      = $serialization ?: new MessageSerialization(new JsonSerialization);
        $this->correlationId      = 0;

        $this->setTimeout($timeout);
    }

    /**
     * Invoke a remote procedure.
     *
     * @param string $name         The name of the procedure to invoke
     * @param mixed  $argument,... The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function invoke($name)
    {
        return $this->invokeArray(
            $name,
            array_slice(func_get_args(), 1)
        );
    }

    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function invokeArray($name, array $arguments)
    {
        $this->initialize();

        ++$this->correlationId;

        $request       = Request::create($name, $arguments);
        $responseQueue = $this->declarationManager->responseQueue();

        if ($this->logger) {
            $this->logger->debug(
                'rpc.client {queue} #{id} request: {request}',
                [
                    'id'      => $this->correlationId,
                    'queue'   => $responseQueue,
                    'request' => $request,
                ]
            );
        }

        $this->send($request);

        $response = $this->wait();

        if (null === $response) {
            if ($this->logger) {
                $this->logger->warning(
                    'rpc.client {queue} #{id} response: TIMEOUT ({timeout} seconds)',
                    [
                        'id'      => $this->correlationId,
                        'queue'   => $responseQueue,
                        'timeout' => $this->timeout,
                    ]
                );
            }

            throw new TimeoutException($name, $this->timeout);
        }

        if ($this->logger) {
            $this->logger->debug(
                'rpc.client {queue} #{id} response: {response}',
                [
                    'id'       => $this->correlationId,
                    'queue'    => $responseQueue,
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
        return $this->invokeArray($name, $arguments);
    }

    /**
     * Get the RPC response timeout.
     *
     * @return integer|float The RPC response timeout in seconds.
     */
    public function timeout()
    {
        return $this->timeout;
    }

    /**
     * Set the RPC response timeout.
     *
     * @param integer|float The RPC response timeout in seconds.
     */
    public function setTimeout($timeout)
    {
        if (!is_numeric($timeout) || $timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than zero.');
        }

        $this->timeout = $timeout;
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
                'expiration'     => strval(
                    intval($this->timeout * 1000)
                ),
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
        $iso     = $this->isolator();
        $start   = $iso->microtime(true);
        $elapsed = 0;

        while (null === $this->response) {
            try {
                $this
                    ->channel
                    ->wait(
                        null,    // allowed methods
                        false,   // non-blocking
                        $this->timeout - $elapsed
                    );
            } catch (AMQPTimeoutException $e) {
                return null;
            }

            $elapsed = $iso->microtime(true) - $start;

            if ($elapsed > $this->timeout) {
                return null;
            }
        }

        // A response was received ...
        $response       = $this->response;
        $this->response = null;

        return $response;
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $timeout;
    private $correlationId;
    private $consumerTag;
    private $response;
}
