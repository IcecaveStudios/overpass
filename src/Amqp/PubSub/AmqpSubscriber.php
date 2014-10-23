<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\PubSub\SubscriberInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use Icecave\Repr\Repr;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;

class AmqpSubscriber implements SubscriberInterface
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
     * Subscribe to the given topic.
     *
     * @param string $topic The topic or topic pattern to subscribe to.
     */
    public function subscribe($topic)
    {
        $normalizedTopic = $this->normalizeTopic($topic);

        if (isset($this->subscriptions[$normalizedTopic])) {
            return;
        }

        $this->channel->queue_bind(
            $this->declarationManager->queue(),
            $this->declarationManager->exchange(),
            $normalizedTopic
        );

        $this->subscriptions[$normalizedTopic] = true;

        if ($this->logger) {
            $this->logger->debug(
                'Subscribed to topic "{topic}"',
                [
                    'topic' => $topic,
                ]
            );
        }
    }

    /**
     * Unsubscribe from the given topic.
     *
     * @param string $topic The topic or topic pattern to unsubscribe from.
     */
    public function unsubscribe($topic)
    {
        $normalizedTopic = $this->normalizeTopic($topic);

        if (!isset($this->subscriptions[$normalizedTopic])) {
            return;
        }

        $this->channel->queue_unbind(
            $this->declarationManager->queue(),
            $this->declarationManager->exchange(),
            $normalizedTopic
        );

        unset($this->subscriptions[$normalizedTopic]);

        if ($this->logger) {
            $this->logger->debug(
                'Unsubscribed from topic "{topic}"',
                [
                    'topic' => $topic,
                ]
            );
        }
    }

    /**
     * Consume messages from subscriptions.
     *
     * When a message is received the callback is invoked with two parameters,
     * the first is the topic to which the message was published, the second is
     * the message payload.
     *
     * The callback must return true in order to keep consuming messages, or
     * false to end consumption.
     *
     * @param callable $callback The callback to invoke when a message is received.
     */
    public function consume(callable $callback)
    {
        if (!$this->subscriptions) {
            return;
        }

        $this->consumerCallback = $callback;

        $this->consumerTag = $this->channel->basic_consume(
            $this->declarationManager->queue(),
            '',    // consumer tag
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            function ($message) {
                $this->dispatch($message);
            }
        );

        while ($this->channel->callbacks) {
            $this->channel->wait();
        }
    }

    /**
     * Convert a topic with wildcard strings into an AMQP-style topic wildcard.
     *
     * @param string $topic
     *
     * @return string
     */
    private function normalizeTopic($topic)
    {
        return strtr(
            $topic,
            [
                '*' => '#',
                '?' => '*',
            ]
        );
    }

    /**
     * Dispatch a received message.
     *
     * @param AMQPMessage $message
     */
    private function dispatch(AMQPMessage $message)
    {
        $payload = $this
            ->serialization
            ->unserialize($message->body);

        $topic = $message->get('routing_key');

        if ($this->logger) {
            $this->logger->debug(
                'Received {payload} from topic "{topic}"',
                [
                    'topic' => $topic,
                    'payload' => Repr::repr($payload),
                ]
            );
        }

        $callback = $this->consumerCallback;

        $keepConsuming = $callback(
            $topic,
            $payload
        );

        if ($keepConsuming) {
            return;
        }

        $this->channel->basic_cancel($this->consumerTag);

        $this->consumerCallback = null;
        $this->consumerTag = null;
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $subscriptions;
    private $consumerCallback;
    private $consumerTag;
}
