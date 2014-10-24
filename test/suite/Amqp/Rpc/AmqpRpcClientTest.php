<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Serialization\JsonSerialization;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AmqpRpcClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->serialization = new JsonSerialization();
        $this->logger = Phake::mock(LoggerInterface::class);
        $this->callback = null;

        // Store the handler as soon as basic_consume is called ...
        Phake::when($this->channel)
            ->basic_consume(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($_, $tag, $_, $_, $_, $_, $callback) {
                    $this->callback = $callback;

                    if ($tag === '') {
                        $tag = '<consumer-tag>';
                    }

                    // store the callback as this is used to determine whether to continue waiting
                    $this->channel->callbacks[$tag] = $callback;

                    return $tag;
                }
            );

        // Simular receiving a message when waiting ...
        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function () {
                    $message = new AMQPMessage(
                        '[0,123]',
                        [
                            'correlation_id' => 1,
                        ]
                    );

                    call_user_func($this->callback, $message);
                }
            )->thenGetReturnByLambda(
                function () {
                    $message = new AMQPMessage(
                        '[0,456]',
                        [
                            'correlation_id' => 2,
                        ]
                    );

                    call_user_func($this->callback, $message);
                }
            );

        Phake::when($this->declarationManager)
            ->responseQueue()
            ->thenReturn('<response-queue>');

        Phake::when($this->declarationManager)
            ->requestQueue(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($name) {
                    return sprintf('<request-queue-%s>', $name);
                }
            );

        $this->client = new AmqpRpcClient(
            $this->channel,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testCall()
    {
        $result = $this->client->call('procedure-name', [1, 2, 3]);

        $callback = null;

        Phake::verify($this->channel)->basic_consume(
            '<response-queue>',
            '',    // consumer tag
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            Phake::capture($callback)
        );

        $this->assertSame(
            $this->callback,
            $callback
        );

        $message = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($message),
            '', // default direct exchange
            '<request-queue-procedure-name>'
        );

        $this->assertEquals(
            new AMQPMessage(
                '["procedure-name",[1,2,3]]',
                [
                    'reply_to' => '<response-queue>',
                    'correlation_id' => 1,
                ]
            ),
            $message
        );

        $this->assertSame(
            123,
            $result
        );
    }

    public function testCallIgnoresPreviousResponses()
    {
        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function () {
                    $message = new AMQPMessage(
                        '<this-should-be-ignored>',
                        [
                            'correlation_id' => 0,
                        ]
                    );

                    call_user_func($this->callback, $message);
                }
            )->thenGetReturnByLambda(
                function () {
                    $message = new AMQPMessage(
                        '[0,123]',
                        [
                            'correlation_id' => 1,
                        ]
                    );

                    call_user_func($this->callback, $message);
                }
            );

        $result = $this->client->call('procedure-name', [1, 2, 3]);

        $message = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($message),
            '', // default direct exchange
            '<request-queue-procedure-name>'
        );

        $this->assertEquals(
            1,
            $message->get('correlation_id')
        );

        $this->assertSame(
            123,
            $result
        );
    }

    public function testCallFailsWithFutureResponses()
    {
        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function () {
                    $message = new AMQPMessage(
                        '<this-should-be-ignored>',
                        [
                            'correlation_id' => 02
                        ]
                    );

                    call_user_func($this->callback, $message);
                }
            );

        $this->setExpectedException(
            RuntimeException::class,
            'Out-of-order RPC response returned by server.'
        );

        $this->client->call('procedure-name', [1, 2, 3]);
    }

    public function testCallInitializesOnce()
    {
        $result = $this->client->call('procedure-1', [1, 2, 3]);
        $result = $this->client->call('procedure-2', [1, 2, 3]);

        Phake::verify($this->channel, Phake::times(1))->basic_consume(
            Phake::anyParameters()
        );
    }

    public function testCallLogging()
    {
        $this->client->setLogger($this->logger);

        $this->client->call('procedure-name', [1, 2, 3]);

        $callback = null;

        Phake::verify($this->channel)->basic_consume(
            '<response-queue>',
            '',    // consumer tag
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            Phake::capture($callback)
        );

        Phake::verify($this->logger)->debug(
            'RPC #{id} {request}',
            [
                'id' => 1,
                'request' => Request::create('procedure-name', [1, 2, 3]),
            ]
        );

        Phake::verify($this->logger)->debug(
            'RPC #{id} {request} -> {response}',
            [
                'id' => 1,
                'request' => Request::create('procedure-name', [1, 2, 3]),
                'response' => Response::create(123),
            ]
        );
    }

    public function testCallMagicMethod()
    {
        $result = $this->client->procedure(1, 2, 3);

        $this->assertSame(
            123,
            $result
        );
    }
}
