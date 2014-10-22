<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;

class AmqpPublisherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(AmqpDeclarationManager::class);
        $this->serialization = Phake::mock(SerializationInterface::class);

        Phake::when($this->declarationManager)
            ->pubSubExchange(Phake::anyParameters())
            ->thenReturn('overpass.pubsub')
            ->thenThrow(new LogicException('Multiple AMQP exchanges created!'));

        Phake::when($this->serialization)
            ->serialize('bar')
            ->thenReturn('<bar>');

        $this->publisher = new AmqpPublisher(
            $this->channel,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testPublish()
    {
        $this->publisher->publish('foo', 'bar');

        Phake::inOrder(
            Phake::verify($this->declarationManager)->pubSubExchange($this->channel),
            Phake::verify($this->channel)->basic_publish(
                new AMQPMessage('<bar>'),
                'overpass.pubsub',
                'foo'
            )
        );
    }

    public function testPublishOnlyInitializesOnce()
    {
        $this->publisher->publish('foo', 'bar');
        $this->publisher->publish('foo', 'bar');

        Phake::verify($this->declarationManager, Phake::times(1))->pubSubExchange($this->channel);
    }
}
