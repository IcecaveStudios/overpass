<?php
namespace Icecave\Overpass\Amqp\PubSub;

use AMQPChannel;
use AMQPConnection;
use AMQPEnvelope;
use AMQPExchange;
use AMQPQueue;
use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use Phake;
use PHPUnit_Framework_TestCase;

class AmqpSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = Phake::mock(AMQPConnection::class);
        $this->declarationManager = Phake::mock(AmqpDeclarationManager::class);
        $this->serialization = Phake::mock(SerializationInterface::class);
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->exchange = Phake::mock(AMQPExchange::class);
        $this->queue = Phake::mock(AMQPQueue::class);
        $this->envelope = Phake::mock(AMQPEnvelope::class);

        Phake::when($this->declarationManager)
            ->channel(Phake::anyParameters())
            ->thenReturn($this->channel)
            ->thenThrow(new LogicException('Multiple AMQP channels created!'));

        Phake::when($this->declarationManager)
            ->pubSubExchange(Phake::anyParameters())
            ->thenReturn($this->exchange)
            ->thenThrow(new LogicException('Multiple AMQP exchanges created!'));

        Phake::when($this->declarationManager)
            ->exclusiveQueue(Phake::anyParameters())
            ->thenReturn($this->queue)
            ->thenThrow(new LogicException('Multiple AMQP queues created!'));

        Phake::when($this->serialization)
            ->unserialize('<bar>')
            ->thenReturn('bar');

        Phake::when($this->exchange)
            ->getName()
            ->thenReturn('<exchange>');

        Phake::when($this->envelope)
            ->getRoutingKey()
            ->thenReturn('foo');

        Phake::when($this->envelope)
            ->getBody()
            ->thenReturn('<bar>');

        $this->subscriber = new AmqpSubscriber(
            $this->connection,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testSubscribe()
    {
        $this->subscriber->subscribe('foo.bar');
        $this->subscriber->subscribe('foo.bar');

        Phake::verify($this->queue, Phake::times(1))->bind(
            '<exchange>',
            'foo.bar'
        );
    }

    public function testSubscribeWithAtomWildcard()
    {
        $this->subscriber->subscribe('foo.?');

        Phake::verify($this->queue)->bind(
            '<exchange>',
            'foo.*'
        );
    }

    public function testSubscribeWithFullWildcard()
    {
        $this->subscriber->subscribe('foo.*');

        Phake::verify($this->queue)->bind(
            '<exchange>',
            'foo.#'
        );
    }

    public function testUnsubscribe()
    {
        $this->subscriber->unsubscribe('foo.bar');
        $this->subscriber->subscribe('foo.bar');
        $this->subscriber->unsubscribe('foo.bar');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.bar'
        );
    }

    public function testUnsubscribeWithAtomWildcard()
    {
        $this->subscriber->unsubscribe('foo.?');
        $this->subscriber->subscribe('foo.?');
        $this->subscriber->unsubscribe('foo.?');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.*'
        );
    }

    public function testUnsubscribeWithFullWildcard()
    {
        $this->subscriber->unsubscribe('foo.*');
        $this->subscriber->subscribe('foo.*');
        $this->subscriber->unsubscribe('foo.*');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.#'
        );
    }

    public function testConsume()
    {
        $calls = [];
        $consumer = function () use (&$calls) {
            $calls[] = func_get_args();

            return true;
        };

        $this->subscriber->subscribe('foo');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->queue)->consume(
            Phake::capture($handler),
            AMQP_AUTOACK,
            'consumer'
        );

        $this->assertTrue(
            is_callable($handler)
        );

        $this->assertTrue(
            $handler($this->envelope)
        );

        $this->assertSame(
            [['foo', 'bar']],
            $calls
        );

        Phake::verify($this->queue, Phake::never())->cancel(Phake::anyParameters());
    }

    public function testConsumeEnd()
    {
        $calls = [];
        $consumer = function () use (&$calls) {
            $calls[] = func_get_args();

            return false;
        };

        $this->subscriber->subscribe('foo');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->queue)->consume(
            Phake::capture($handler),
            AMQP_AUTOACK,
            'consumer'
        );

        $this->assertTrue(
            is_callable($handler)
        );

        $this->assertFalse(
            $handler($this->envelope)
        );

        $this->assertSame(
            [['foo', 'bar']],
            $calls
        );

        Phake::verify($this->queue)->cancel('consumer');
    }

    public function testConsumeWithNoSubscriptions()
    {
        $calls = [];
        $consumer = function () use (&$calls) {
            $calls[] = func_get_args();

            return false;
        };

        $this->subscriber->consume($consumer);

        Phake::verifyNoInteraction($this->queue);

        $this->assertSame(
            [],
            $calls
        );
    }
}
