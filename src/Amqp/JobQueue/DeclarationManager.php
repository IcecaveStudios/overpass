<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @access private
 */
class DeclarationManager
{
    public function __construct(AMQPChannel $channel)
    {
        $this->channel = $channel;
        $this->jobQueues = [];
    }

    /**
     * @return string
     */
    public function exchange()
    {
        if ($this->exchange) {
            return $this->exchange;
        }

        $name = 'overpass/job';

        $this
            ->channel
            ->exchange_declare(
                $name,
                'direct',
                false, // passive,
                false, // durable,
                false  // auto delete
            );

        $this->exchange = $name;

        return $this->exchange;
    }

    /**
     * Decalare and bind to a job queue.
     *
     * @param string $jobName
     */
    public function jobQueue($jobName)
    {
        if (isset($this->jobQueues[$jobName])) {
            return $this->jobQueues[$jobName];
        }

        list($queueName) = $this
            ->channel
            ->queue_declare(
                'overpass/job/' . $jobName,
                false, // passive
                false, // durable
                false, // exclusive
                false  // auto delete
            );

        $this
            ->channel
            ->queue_bind(
                $queueName,
                $this->exchange(),
                $jobName
            );

        $this->jobQueues[$jobName] = $queueName;
    }

    private $exchange;
    private $jobQueues;
}
