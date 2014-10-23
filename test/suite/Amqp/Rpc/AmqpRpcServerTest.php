<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use Icecave\Overpass\Rpc\ProcedureInterface;
use Icecave\Overpass\Rpc\Registry;
use Icecave\Overpass\Serialization\JsonSerialization;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use RuntimeException;

class AmqpRpcServerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->registry = Phake::partialMock(Registry::class);
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->serialization = new JsonSerialization;
        $this->procedure1 = Phake::mock(ProcedureInterface::class);
        $this->procedure2 = Phake::mock(ProcedureInterface::class);
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

        Phake::when($this->procedure1)
            ->invoke(Phake::anyParameters())
            ->thenReturn('<procedure-1-result>');

        Phake::when($this->procedure2)
            ->invoke(Phake::anyParameters())
            ->thenReturn('<procedure-2-result>');

        $this->server = new AmqpRpcServer(
            $this->registry,
            $this->channel,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testRegistry()
    {
        $this->assertSame(
            $this->registry,
            $this->server->registry()
        );
    }

    public function testRun()
    {
        $this->registry->register('procedure-1', $this->procedure1);
        $this->registry->register('procedure-2', $this->procedure2);

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
        Phake::verify($this->channel)->basic_cancel('<consumer-tag-1>');
        Phake::verify($this->channel)->basic_cancel('<consumer-tag-2>');

        $this->assertTrue(
            is_callable($handler)
        );
    }

    public function testRunWithEmptyRegistry()
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

                    Phake::verify($this->channel)->basic_cancel('<consumer-tag-1>');
                    Phake::verify($this->channel)->basic_cancel('<consumer-tag-2>');

                    $this->channel->callbacks = [];
                }
            );

        $this->registry->register('procedure-1', $this->procedure1);
        $this->registry->register('procedure-2', $this->procedure2);

        $this->server->run();
    }

    public function testReceiveRequest()
    {
        $this->registry->register('procedure-name', $this->procedure1);

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
            Phake::verify($this->procedure1)->invoke([1, 2, 3]),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::SUCCESS . ',"<procedure-1-result>"]',
                [
                    'correlation_id' => 456,
                ]
            ),
            $responseMessage
        );
    }

    public function testReceiveRequestWithProcedureException()
    {
        $exception = new RuntimeException('The procedure failed!');

        Phake::when($this->procedure1)
            ->invoke(Phake::anyParameters())
            ->thenThrow($exception);

        $this->registry->register('procedure-name', $this->procedure1);

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
            Phake::verify($this->procedure1)->invoke([1, 2, 3]),
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

    public function testReceiveRequestWithoutReplyQueue()
    {
        $this->registry->register('procedure-name', $this->procedure1);

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

        Phake::inOrder(
            Phake::verify($this->channel)->basic_ack('<delivery-tag>'),
            Phake::verify($this->procedure1)->invoke([])
        );

        Phake::verify($this->channel, Phake::never())->basic_publish(
            Phake::anyParameters()
        );
    }

    public function testReceiveRequestWithoutCorrelationId()
    {
        $this->registry->register('procedure-name', $this->procedure1);

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
            Phake::verify($this->procedure1)->invoke([]),
            Phake::verify($this->channel)->basic_publish(
                Phake::capture($responseMessage),
                '', // default direct exchange
                '<response-queue>'
            )
        );

        $this->assertEquals(
            new AMQPMessage(
                '[' . ResponseCode::SUCCESS . ',"<procedure-1-result>"]'
            ),
            $responseMessage
        );
    }

    public function testReceiveRequestWithUnknownProcedure()
    {
        $this->registry->register('procedure-name', $this->procedure1);

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
            '["unknown-procedure-name",[]]',
            [
                'reply_to' => '<response-queue>',
                'correlation_id' => 456,
            ]
        );

        $requestMessage->delivery_info['delivery_tag'] = '<delivery-tag>';

        $handler($requestMessage);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', true);
    }
}
