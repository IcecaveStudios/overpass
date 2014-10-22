<?php
namespace Icecave\Overpass\Amqp;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @internal
 */
class AmqpDeclarationManager
{
    /**
     * @param AMQPChannel $channel
     *
     * @return string
     */
    public function pubSubExchange(AMQPChannel $channel)
    {
        $name = 'overpass.pubsub';

        $channel->exchange_declare(
            $name,
            'topic',
            false, // passive,
            false, // durable,
            false  // auto delete
        );

        return $name;
    }

    /**
     * @param AMQPChannel $channel
     *
     * @return string
     */
    public function exclusiveQueue(AMQPChannel $channel)
    {
        list($queueName) = $channel->queue_declare(
            '',    // name
            false, // passive
            false, // durable,
            true   // exclusive
        );

        return $queueName;
    }

    /**
     * @param AMQPChannel $channel
     *
     * @return string
     */
    public function rpcQueue(AMQPChannel $channel, $name)
    {
        list($queueName) = $channel->queue_declare(
            'overpass.rpc.' . $name
        );

        return $queueName;
    }
}
