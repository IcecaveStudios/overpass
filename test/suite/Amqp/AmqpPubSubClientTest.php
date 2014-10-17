<?php
namespace Icecave\Overpass\Amqp;

use AMQPChannel;
use AMQPConnection;
use AMQPEnvelope;
use AMQPExchange;
use AMQPQueue;
use Eloquent\Phony\Phpunit\Phony;
use Icecave\Isolator\Isolator;
use InvalidArgumentException;
use LogicException;
use Phake;
use PHPUnit_Framework_TestCase;

class AmqpPubSubClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = Phake::mock(AMQPConnection::class);
        $this->isolator = Phake::mock(Isolator::class);

        $this->channel = Phake::mock(AMQPChannel::class);
        $this->exchange = Phake::mock(AMQPExchange::class);
        $this->queue = Phake::mock(AMQPQueue::class);
        $this->envelope = Phake::mock(AMQPEnvelope::class);
        $this->consumer = Phony::stub();

        Phake::when($this->isolator)
            ->new(AMQPChannel::class, Phake::anyParameters())
            ->thenReturn($this->channel)
            ->thenThrow(new LogicException('Multiple AMQP channels created!'));

        Phake::when($this->isolator)
            ->new(AMQPExchange::class, Phake::anyParameters())
            ->thenReturn($this->exchange)
            ->thenThrow(new LogicException('Multiple AMQP exchanges created!'));

        Phake::when($this->isolator)
            ->new(AMQPQueue::class, Phake::anyParameters())
            ->thenReturn($this->queue)
            ->thenThrow(new LogicException('Multiple AMQP queues created!'));

        Phake::when($this->exchange)
            ->getName()
            ->thenReturn('<exchange>');

        Phake::when($this->envelope)
            ->getRoutingKey()
            ->thenReturn('foo');

        Phake::when($this->envelope)
            ->getBody()
            ->thenReturn('"bar"');

        $this->client = new AmqpPubSubClient($this->connection);
        $this->client->setIsolator($this->isolator);
    }

    public function testPublish()
    {
        $this->client->publish('foo', 'bar');

        Phake::verify($this->exchange)->publish(
            '"bar"',
            'foo'
        );
    }

    public function testPublishWithSerializationFailure()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'Could not serialize payload.'
        );

        $this->client->publish('foo', fopen(__FILE__, 'r'));
    }

    public function testSubscribe()
    {
        $this->client->subscribe('foo.bar');
        $this->client->subscribe('foo.bar');

        Phake::verify($this->queue, Phake::times(1))->bind(
            '<exchange>',
            'foo.bar'
        );
    }

    public function testSubscribeWithAtomWildcard()
    {
        $this->client->subscribe('foo.?');

        Phake::verify($this->queue)->bind(
            '<exchange>',
            'foo.*'
        );
    }

    public function testSubscribeWithFullWildcard()
    {
        $this->client->subscribe('foo.*');

        Phake::verify($this->queue)->bind(
            '<exchange>',
            'foo.#'
        );
    }

    public function testUnsubscribe()
    {
        $this->client->unsubscribe('foo.bar');
        $this->client->subscribe('foo.bar');
        $this->client->unsubscribe('foo.bar');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.bar'
        );
    }

    public function testUnsubscribeWithAtomWildcard()
    {
        $this->client->unsubscribe('foo.?');
        $this->client->subscribe('foo.?');
        $this->client->unsubscribe('foo.?');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.*'
        );
    }

    public function testUnsubscribeWithFullWildcard()
    {
        $this->client->unsubscribe('foo.*');
        $this->client->subscribe('foo.*');
        $this->client->unsubscribe('foo.*');

        Phake::verify($this->queue, Phake::times(1))->unbind(
            '<exchange>',
            'foo.#'
        );
    }

    public function testConsume()
    {
        $this
            ->consumer
            ->returns(true);

        $this->client->subscribe('foo');
        $this->client->consume($this->consumer);

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

        $this
            ->consumer
            ->calledWith('foo', 'bar');

        Phake::verify($this->queue, Phake::never())->cancel(Phake::anyParameters());
    }

    public function testConsumeEnd()
    {
        $this
            ->consumer
            ->returns(false);

        $this->client->subscribe('foo');
        $this->client->consume($this->consumer);

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

        $this
            ->consumer
            ->calledWith('foo', 'bar');

        Phake::verify($this->queue)->cancel('consumer');
    }

    public function testConsumeNullValue()
    {
        Phake::when($this->envelope)
            ->getBody()
            ->thenReturn(' null '); // JSON packet

        $this->client->subscribe('foo');
        $this->client->consume($this->consumer);

        $handler = null;

        Phake::verify($this->queue)->consume(
            Phake::capture($handler),
            AMQP_AUTOACK,
            'consumer'
        );

        $handler($this->envelope);

        $this
            ->consumer
            ->calledWith('foo', null);
    }

    public function testConsumeWithUnserializationFailure()
    {
        Phake::when($this->envelope)
            ->getBody()
            ->thenReturn(']['); // invalid JSON

        $this->client->subscribe('foo');
        $this->client->consume($this->consumer);

        $handler = null;

        Phake::verify($this->queue)->consume(
            Phake::capture($handler),
            AMQP_AUTOACK,
            'consumer'
        );

        $this->setExpectedException(
            InvalidArgumentException::class,
            'Could not unserialize payload.'
        );

        try {
            $handler($this->envelope);
        } finally {
            $this
                ->consumer
                ->never()
                ->called();
        }

    }

    public function testConsumeWithNoSubscriptions()
    {
        $this->client->consume($this->consumer);

        Phake::verifyNoInteraction($this->queue);

        $this
            ->consumer
            ->never()
            ->called();
    }

    public function testChannel()
    {
        $this->client->publish('foo', 'bar');

        Phake::verify($this->isolator)->new(AMQPChannel::class, $this->connection);

        Phake::verify($this->channel)->setPrefetchCount(1);
    }

    public function testExchange()
    {
        $this->client->publish('foo', 'bar');

        Phake::verify($this->isolator)->new(AMQPExchange::class, $this->channel);

        Phake::verify($this->exchange)->setName('overpass.pubsub');
        Phake::verify($this->exchange)->setType(AMQP_EX_TYPE_TOPIC);
        Phake::verify($this->exchange)->declareExchange();
    }

    public function testQueue()
    {
        $this->client->subscribe('foo');

        Phake::verify($this->isolator)->new(AMQPQueue::class, $this->channel);

        Phake::verify($this->queue)->setFlags(AMQP_EXCLUSIVE);
        Phake::verify($this->queue)->declareQueue();
    }
}
