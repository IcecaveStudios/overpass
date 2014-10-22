<?php
namespace Icecave\Overpass\Amqp\Rpc;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @internal
 */
class DeclarationManager
{
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->requestQueues = [];
    }

    /**
     * @param string $procedureName
     *
     * @return string
     */
    public function requestQueue($procedureName)
    {
        if (isset($this->requestQueues[$procedureName])) {
            return $this->requestQueues[$procedureName];
        }

        list($queueName) = $this->channel->queue_declare(
            'overpass.rpc.' . $procedureName,
            false, // passive
            false, // durable
            false, // exclusive
            false  // auto delete
        );

        $this->requestQueues[$procedureName] = $queueName;

        return $queueName;
    }

    /**
     * @return string
     */
    public function responseQueue()
    {
        if ($this->responseQueue) {
            return $this->responseQueue;
        }

        list($this->responseQueue) = $this->channel->queue_declare(
            '',
            false, // passive
            false, // durable
            true,  // exclusive
            false  // auto delete
        );

        return $this->responseQueue;
    }

    private $requestQueues;
    private $responseQueue;
}
