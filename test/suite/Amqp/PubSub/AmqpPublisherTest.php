<?php
namespace Icecave\Overpass\Amqp\PubSub;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use Phake;
use PHPUnit_Framework_TestCase;

class AmqpPublisherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->connection = Phake::mock(AMQPConnection::class);
        $this->declarationManager = Phake::mock(AmqpDeclarationManager::class);
        $this->serialization = Phake::mock(SerializationInterface::class);
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->exchange = Phake::mock(AMQPExchange::class);

        Phake::when($this->declarationManager)
            ->channel(Phake::anyParameters())
            ->thenReturn($this->channel)
            ->thenThrow(new LogicException('Multiple AMQP channels created!'));

        Phake::when($this->declarationManager)
            ->pubSubExchange(Phake::anyParameters())
            ->thenReturn($this->exchange)
            ->thenThrow(new LogicException('Multiple AMQP exchanges created!'));

        Phake::when($this->serialization)
            ->serialize('bar')
            ->thenReturn('<bar>');

        $this->publisher = new AmqpPublisher(
            $this->connection,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testPublish()
    {
        $this->publisher->publish('foo', 'bar');

        Phake::verify($this->exchange)->publish(
            '<bar>',
            'foo'
        );
    }

    public function testPublishOnlyInitializesOnce()
    {
        $this->publisher->publish('foo', 'bar');
        $this->publisher->publish('foo', 'bar');

        Phake::verify($this->exchange, Phake::times(2))->publish(
            '<bar>',
            'foo'
        );
    }
}
