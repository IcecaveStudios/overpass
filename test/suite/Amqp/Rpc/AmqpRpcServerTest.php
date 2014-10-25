<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\Message\ResponseCode;
use LogicException;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AmqpRpcServerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->logger = Phake::mock(LoggerInterface::class);
        $this->procedure1 = function () { return '<procedure-1: ' . implode(', ', func_get_args()) . '>'; };
        $this->procedure2 = function () { return '<procedure-2: ' . implode(', ', func_get_args()) . '>'; };
        $this->procedure3 = function () { throw new RuntimeException('The procedure failed!'); };
        $this->consumerTagCounter = 0;

        Phake::when($this->channel)
            ->basic_consume(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($_, $tag, $_, $_, $_, $_, $callback) {
                    if ($tag === '') {
                        $tag = '<consumer-tag-' . ++$this->consumerTagCounter . '>';
                    }

                    // store the callback as this is used to determine whether to continue waiting
                    $this->channel->callbacks[$tag] = $callback;

                    return $tag;
                }
            );

        Phake::when($this->channel)
            ->basic_cancel(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($tag) {
                    unset($this->channel->callbacks[$tag]);
                }
            );

        Phake::when($this->channel)
            ->wait()
            ->thenReturn(null)
            ->thenGetReturnByLambda(
                function () {
                    // unset the callback so the consumer stops waiting
                    $this->channel->callbacks = [];
                }
            );

        Phake::when($this->declarationManager)
            ->requestQueue(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($name) {
                    return sprintf('<request-queue-%s>', $name);
                }
            );

        $this->server = new AmqpRpcServer(
            $this->logger,
            $this->channel,
            $this->declarationManager
        );
    }

    public function testExposeWhileRunning()
    {
        Phake::when($this->channel)
            ->wait()
            ->thenGetReturnByLambda(
                function () {
                    $this->server->expose('procedure-2', $this->procedure2);
                }
            )->thenGetReturnByLambda(
                function () {
                    // unset the callback so the consumer stops waiting
                    $this->channel->callbacks = [];
                }
            );

        $this->server->expose('procedure-1', $this->procedure1);

        $this->setExpectedException(
            LogicException::class,
            'Procedures can not be exposed while the server is running.'
        );

        $this->server->run();

        $this->server->expose('procedure-2', $this->procedure2);
    }

    public function testRun()
    {
        $this->server->expose('procedure-1', $this->procedure1);
        $this->server->expose('procedure-2', $this->procedure2);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-1>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-2>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            $handler
        );

        Phake::verify($this->channel, Phake::times(2))->wait();

        $this->assertTrue(
            is_callable($handler)
        );
    }

    public function testRunNoProcedures()
    {
        $this->server->run();

        Phake::verifyNoInteraction($this->channel);
    }

    public function testStop()
    {
        Phake::when($this->channel)
            ->wait()
            ->thenGetReturnByLambda(
                function () {
                    $this->server->stop();
                }
            );

        $this->server->expose('procedure-1', $this->procedure1);
        $this->server->expose('procedure-2', $this->procedure2);

        $this->server->run();

        Phake::verify($this->channel)->basic_cancel('<consumer-tag-1>');
        Phake::verify($this->channel)->basic_cancel('<consumer-tag-2>');
    }

    public function testReceiveRequest()
    {
        $this->server->expose('procedure-name', $this->procedure1);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-name>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $requestMessage = new AMQPMessage(
            '["procedure-name",[1,2,3]]',
            [
                'reply_to' => '<response-queue>',
                'correlation_id' => 456,
            ]
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        $responseMessage = null;

        Phake::inOrder(
            Phake::verify($this->channel)->basic_ack('<delivery-tag>'),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::SUCCESS . ',"<procedure-1: 1, 2, 3>"]',
                [
                    'correlation_id' => 456,
                ]
            ),
            $responseMessage
        );
    }

    public function testReceiveRequestWithProcedureException()
    {
        $this->server->expose('procedure-name', $this->procedure3);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-name>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $requestMessage = new AMQPMessage(
            '["procedure-name",[1,2,3]]',
            [
                'reply_to' => '<response-queue>',
            ]
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        $responseMessage = null;

        Phake::inOrder(
            Phake::verify($this->channel)->basic_ack('<delivery-tag>'),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::EXCEPTION . ',"The procedure failed!"]'
            ),
            $responseMessage
        );
    }

    public function testReceiveRequestWithInvalidMessage()
    {
        $this->server->expose('procedure-name', $this->procedure3);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-name>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $requestMessage = new AMQPMessage(
            '[null]',
            [
                'reply_to' => '<response-queue>',
            ]
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        $responseMessage = null;

        Phake::inOrder(
            Phake::verify($this->channel)->basic_ack('<delivery-tag>'),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::INVALID_MESSAGE . ',"Request payload must be a 2-tuple."]'
            ),
            $responseMessage
        );
    }

    public function testReceiveRequestWithoutReplyQueue()
    {
        $this->server->expose('procedure-name', $this->procedure1);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-name>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $requestMessage = new AMQPMessage(
            '["procedure-name",[]]'
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        Phake::verify($this->channel, Phake::never())->basic_publish(
            Phake::anyParameters()
        );
    }

    public function testReceiveRequestWithoutCorrelationId()
    {
        $this->server->expose('procedure-name', $this->procedure1);

        $this->server->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<request-queue-procedure-name>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $requestMessage = new AMQPMessage(
            '["procedure-name",[]]',
            [
                'reply_to' => '<response-queue>',
            ]
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        $responseMessage = null;

        Phake::inOrder(
            Phake::verify($this->channel)->basic_ack('<delivery-tag>'),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::SUCCESS . ',"<procedure-1: >"]'
            ),
            $responseMessage
        );
    }
}
