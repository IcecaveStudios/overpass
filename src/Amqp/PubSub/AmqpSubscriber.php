<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\PubSub\SubscriberInterface;
use Icecave\Overpass\Serialization\SerializationInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpSubscriber implements SubscriberInterface
{
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
        $topic = $this->normalizeTopic($topic);

        if (isset($this->subscriptions[$topic])) {
            return;
        }

        $this->channel->queue_bind(
            $this->declarationManager->queue(),
            $this->declarationManager->exchange(),
            $topic
        );

        $this->subscriptions[$topic] = true;
    }

    /**
     * Unsubscribe from the given topic.
     *
     * @param string $topic The topic or topic pattern to unsubscribe from.
     */
    public function unsubscribe($topic)
    {
        $topic = $this->normalizeTopic($topic);

        if (!isset($this->subscriptions[$topic])) {
            return;
        }

        $this->channel->queue_unbind(
            $this->declarationManager->queue(),
            $this->declarationManager->exchange(),
            $topic
        );

        unset($this->subscriptions[$topic]);
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

        $callback = $this->consumerCallback;

        $keepConsuming = $callback(
            $message->get('routing_key'),
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
