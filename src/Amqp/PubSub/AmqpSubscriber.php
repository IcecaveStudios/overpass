<?php
namespace Icecave\Overpass\Amqp\PubSub;

use AMQPConnection;
use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\PubSub\SubscriberInterface;
use Icecave\Overpass\Serialization\SerializationInterface;

class AmqpSubscriber implements SubscriberInterface
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
        $this->subscriptions = [];
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

        $this->queue->bind(
            $this->exchange->getName(),
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

        $this->queue->unbind(
            $this->exchange->getName(),
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

        $consumerTag = 'consumer';

        $handler = function ($envelope) use ($callback, $consumerTag) {

            $keepConsuming = $callback(
                $envelope->getRoutingKey(),
                $this->serialization->unserialize($envelope->getBody())
            );

            if ($keepConsuming) {
                return true;
            }

            $this->queue->cancel($consumerTag);

            return false;
        };

        $this->queue->consume(
            $handler,
            AMQP_AUTOACK,
            $consumerTag
        );
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

        $channel = $this->declarationManager->channel($this->connection);
        $this->exchange = $this->declarationManager->pubSubExchange($channel);
        $this->queue = $this->declarationManager->exclusiveQueue($channel);
    }

    private $connection;
    private $declarationManager;
    private $serialization;
    private $subscriptions;
    private $exchange;
    private $queue;
}
