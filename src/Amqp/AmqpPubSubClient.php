<?php
namespace Icecave\Overpass\Amqp;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Icecave\Isolator\IsolatorTrait;
use Icecave\Overpass\PubSubClientInterface;
use InvalidArgumentException;

class AmqpPubSubClient implements PubSubClientInterface
{
    use IsolatorTrait;

    public function __construct(AMQPConnection $connection)
    {
        $this->subscriptions = [];
        $this->connection = $connection;
    }

    /**
     * Publish a message.
     *
     * @param string $topic   The topic that the payload is published to.
     * @param mixed  $payload The payload to publish.
     */
    public function publish($topic, $payload)
    {
        $this
            ->exchange()
            ->publish(
                $this->serialize($payload),
                $topic
            );
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

        $this
            ->queue()
            ->bind(
                self::EXCHANGE_NAME,
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

        $this
            ->queue()
            ->unbind(
                self::EXCHANGE_NAME,
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
                $this->unserialize($envelope->getBody())
            );

            if ($keepConsuming) {
                return true;
            }

            $this
                ->queue()
                ->cancel($consumerTag);

            return false;
        };

        $this
            ->queue()
            ->consume(
                $handler,
                AMQP_AUTOACK,
                $consumerTag
            );
    }

    /**
     * @return AMQPChannel
     */
    private function channel()
    {
        if (!$this->channel) {
            $this->channel = $this->isolator()->new(
                AMQPChannel::class,
                $this->connection
            );

            $this->channel->setPrefetchCount(1);
        }

        return $this->channel;
    }

    /**
     * @return AMQPExchange
     */
    private function exchange()
    {
        if (!$this->exchange) {
            $this->exchange = $this->isolator()->new(
                AMQPExchange::class,
                $this->channel()
            );

            $this->exchange->setName(self::EXCHANGE_NAME);
            $this->exchange->setType(AMQP_EX_TYPE_TOPIC);
            $this->exchange->declareExchange();
        }

        return $this->exchange;
    }

    /**
     * @return AMQPQueue
     */
    private function queue()
    {
        if (!$this->queue) {
            $this->queue = $this->isolator()->new(
                AMQPQueue::class,
                $this->channel()
            );

            $this->queue->setFlags(AMQP_EXCLUSIVE);
            $this->queue->declareQueue();
        }

        return $this->queue;
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

    /**
     * @param mixed $payload
     *
     * @return string
     */
    private function serialize($payload)
    {
        $buffer = @json_encode($payload);

        if (is_string($buffer)) {
            return $buffer;
        }

        throw new InvalidArgumentException('Could not serialize payload.');
    }

    /**
     * @param string $buffer
     *
     * @return mixed
     */
    private function unserialize($buffer)
    {
        $payload = @json_decode($buffer);

        if ($payload !== null) {
            return $payload;
        } elseif (strcasecmp(trim($buffer), 'null') === 0) {
            return false;
        }

        throw new InvalidArgumentException('Could not unserialize payload.');
    }

    const EXCHANGE_NAME = 'overpass.pubsub';

    private $subscriptions;
    private $connection;
    private $channel;
    private $exchange;
    private $queue;
}
