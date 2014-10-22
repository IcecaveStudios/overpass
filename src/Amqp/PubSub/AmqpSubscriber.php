<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\PubSub\SubscriberInterface;
use Icecave\Overpass\Serialization\SerializationInterface;
use PhpAmqpLib\Channel\AMQPChannel;

class AmqpSubscriber implements SubscriberInterface
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

        $this->initialize();

        $this->channel->queue_bind(
            $this->queue,
            $this->exchange,
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

        $this->initialize();

        $this->channel->queue_unbind(
            $this->queue,
            $this->exchange,
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

        $tag = 'consumer-tag';

        $handler = function ($message) use ($callback, $tag) {

            $keepConsuming = $callback(
                $message->get('routing_key'),
                $this->serialization->unserialize($message->body)
            );

            if ($keepConsuming) {
                return;
            }

            $this->channel->basic_cancel($tag);
        };

        $this->channel->basic_consume(
            $this->queue,
            $tag,
            false, // no local
            true,  // no ack
            false, // exclusive
            false, // no wait
            $handler
        );

        while (
            isset($this->channel->callbacks[$tag])
        ) {
            $this->channel->wait();
        }
    }

    /**
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

    private function initialize()
    {
        if ($this->exchange) {
            return;
        }

        $this->exchange = $this->declarationManager->pubSubExchange($this->channel);
        $this->queue = $this->declarationManager->exclusiveQueue($this->channel);
    }

    private $channel;
    private $exchange;
    private $queue;
    private $declarationManager;
    private $serialization;
    private $subscriptions;
}
