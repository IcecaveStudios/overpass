<?php
namespace Icecave\Overpass\Amqp\PubSub;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @internal
 */
class DeclarationManager
{
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
    }

    /**
     * @return string
     */
    public function exchange()
    {
        if ($this->exchange) {
            return $this->exchange;
        }

        $name = 'overpass.pubsub';

        $this->channel->exchange_declare(
            $name,
            'topic',
            false, // passive,
            false, // durable,
            false  // auto delete
        );

        $this->exchange = $name;

        return $this->exchange;
    }

    /**
     * @return string
     */
    public function queue()
    {
        if ($this->queue) {
            return $this->queue;
        }

        list($this->queue) = $this->channel->queue_declare(
            '',    // name
            false, // passive
            false, // durable,
            true   // exclusive
        );

        return $this->queue;
    }

    private $exchange;
    private $queue;
}
