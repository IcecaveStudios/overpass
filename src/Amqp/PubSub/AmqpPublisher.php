<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\PubSub\PublisherInterface;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpPublisher implements PublisherInterface
{
    /**
     * @param AMQPChannel            $channel
     * @param AmqpDeclarationManager $declarationManager
     * @param SerializationInterface $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        AmqpDeclarationManager $declarationManager,
        SerializationInterface $serialization
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager;
        $this->serialization = $serialization;
    }

    /**
     * Publish a message.
     *
     * @param string $topic   The topic that the payload is published to.
     * @param mixed  $payload The payload to publish.
     */
    public function publish($topic, $payload)
    {
        $this->initialize();

        $message = new AMQPMessage(
            $this->serialization->serialize($payload)
        );

        $this->channel->basic_publish(
            $message,
            $this->exchange,
            $topic
        );
    }

    private function initialize()
    {
        if ($this->exchange) {
            return;
        }

        $this->exchange = $this->declarationManager->pubSubExchange($this->channel);
    }

    private $channel;
    private $exchange;
    private $declarationManager;
    private $serialization;
}
