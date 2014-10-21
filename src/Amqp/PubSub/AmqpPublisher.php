<?php
namespace Icecave\Overpass\Amqp\PubSub;

use AMQPConnection;
use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\PubSub\PublisherInterface;
use Icecave\Overpass\Serialization\SerializationInterface;

class AmqpPublisher implements PublisherInterface
{
    /**
     * @param AMQPConnection         $connection
     * @param AmqpDeclarationManager $declarationManager
     * @param SerializationInterface $serialization
     */
    public function __construct(
        AMQPConnection $connection,
        AmqpDeclarationManager $declarationManager,
        SerializationInterface $serialization
    ) {
        $this->connection = $connection;
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

        $this->exchange->publish(
            $this->serialization->serialize($payload),
            $topic
        );
    }

    private function initialize()
    {
        if ($this->exchange) {
            return;
        }

        $channel = $this->declarationManager->channel($this->connection);
        $this->exchange = $this->declarationManager->pubSubExchange($channel);
    }

    private $connection;
    private $declarationManager;
    private $serialization;
    private $exchange;
}
