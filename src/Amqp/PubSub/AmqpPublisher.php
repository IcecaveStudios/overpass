<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\PubSub\PublisherInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use Icecave\Repr\Repr;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;

class AmqpPublisher implements PublisherInterface
{
    use LoggerAwareTrait;

    /**
     * @param AMQPChannel                 $channel
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        SerializationInterface $serialization = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JsonSerialization();
    }

    /**
     * Publish a message.
     *
     * @param string $topic   The topic that the payload is published to.
     * @param mixed  $payload The payload to publish.
     */
    public function publish($topic, $payload)
    {
        $message = new AMQPMessage(
            $this
                ->serialization
                ->serialize($payload)
        );

        $this->channel->basic_publish(
            $message,
            $this->declarationManager->exchange(),
            $topic
        );

        if ($this->logger) {
            $this->logger->debug(
                'Published {payload} to topic "{topic}"',
                [
                    'topic' => $topic,
                    'payload' => Repr::repr($payload),
                ]
            );
        }
    }

    private $channel;
    private $declarationManager;
    private $serialization;
}
