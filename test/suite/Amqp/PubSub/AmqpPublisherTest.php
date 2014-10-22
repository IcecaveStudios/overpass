<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Serialization\SerializationInterface;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;

class AmqpPublisherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->serialization = Phake::mock(SerializationInterface::class);

        Phake::when($this->declarationManager)
            ->exchange()
            ->thenReturn('<exchange>');

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
        $this->publisher->publish('subscription-topic', 'bar');

        Phake::inOrder(
            Phake::verify($this->declarationManager)->exchange(),
            Phake::verify($this->channel)->basic_publish(
                new AMQPMessage('<bar>'),
                '<exchange>',
                'subscription-topic'
            )
        );
    }
}
