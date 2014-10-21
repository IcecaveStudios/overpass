<?php
namespace Icecave\Overpass\Amqp\PubSub;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Icecave\Isolator\Isolator;
use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Phake;
use PHPUnit_Framework_TestCase;

class AmqpDeclarationManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->isolator = Phake::mock(Isolator::class);
        $this->connection = Phake::mock(AMQPConnection::class);
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->exchange = Phake::mock(AMQPExchange::class);
        $this->queue = Phake::mock(AMQPQueue::class);

        Phake::when($this->isolator)
            ->new(AMQPChannel::class, Phake::anyParameters())
            ->thenReturn($this->channel);
        Phake::when($this->isolator)
            ->new(AMQPExchange::class, Phake::anyParameters())
            ->thenReturn($this->exchange);
        Phake::when($this->isolator)
            ->new(AMQPQueue::class, Phake::anyParameters())
            ->thenReturn($this->queue);

        $this->declarationManager = new AmqpDeclarationManager();
        $this->declarationManager->setIsolator($this->isolator);
    }

    public function testChannel()
    {
        $channel = $this->declarationManager->channel($this->connection);

        $this->assertSame(
            $this->channel,
            $channel
        );

        Phake::verify($this->isolator)->new(AMQPChannel::class, $this->connection);
        Phake::verify($this->channel)->setPrefetchCount(1);
    }

    public function testPubSubExchange()
    {
        $exchange = $this->declarationManager->pubSubExchange($this->channel);

        $this->assertSame(
            $this->exchange,
            $exchange
        );

        Phake::verify($this->isolator)->new(AMQPExchange::class, $this->channel);

        Phake::inOrder(
            Phake::verify($this->exchange)->setName('overpass.pubsub'),
            Phake::verify($this->exchange)->setType(AMQP_EX_TYPE_TOPIC),
            Phake::verify($this->exchange)->declareExchange()
        );
    }

    public function testExclusiveQueue()
    {
        $queue = $this->declarationManager->exclusiveQueue($this->channel);

        $this->assertSame(
            $this->queue,
            $queue
        );

        Phake::verify($this->isolator)->new(AMQPQueue::class, $this->channel);

        Phake::inOrder(
            Phake::verify($this->queue)->setFlags(AMQP_EXCLUSIVE),
            Phake::verify($this->queue)->declareQueue()
        );
    }
}
