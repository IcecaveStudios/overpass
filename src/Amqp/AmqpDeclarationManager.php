<?php
namespace Icecave\Overpass\Amqp;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Icecave\Isolator\IsolatorTrait;

/**
 * @internal
 */
class AmqpDeclarationManager
{
    use IsolatorTrait;

    /**
     * @param AMQPConnection $connection
     *
     * @return AMQPChannel
     */
    public function channel(AMQPConnection $connection)
    {
        $channel = $this->isolator()->new(AMQPChannel::class, $connection);
        $channel->setPrefetchCount(1);

        return $channel;
    }

    /**
     * @param AMQPChannel $channel
     *
     * @return AMQPExchange
     */
    public function pubSubExchange(AMQPChannel $channel)
    {
        $exchange = $this->isolator()->new(AMQPExchange::class, $channel);
        $exchange->setName('overpass.pubsub');
        $exchange->setType(AMQP_EX_TYPE_TOPIC);
        $exchange->declareExchange();

        return $exchange;
    }

    /**
     * @param AMQPChannel $channel
     *
     * @return AMQPQueue
     */
    public function exclusiveQueue(AMQPChannel $channel)
    {
        $queue = $this->isolator()->new(AMQPQueue::class, $channel);
        $queue->setFlags(AMQP_EXCLUSIVE);
        $queue->declareQueue();

        return $queue;
    }
}
