<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Icecave\Overpass\Amqp\DeclarationManagerInterface;
use Icecave\Overpass\Amqp\Exception\HeartbeatFailureException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;

/**
 * @access private
 */
class DeclarationManager implements DeclarationManagerInterface
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

    /**
     * Fake a heartbeat.
     *
     * @return string
     */
    public function heartbeat()
    {
        try {
            $this->exchange = null;

            return $this->exchange();
        } catch (AMQPRuntimeException $ex) {
            throw new HeartbeatFailureException($ex->getMessage());
        } catch (AMQPConnectionClosedException $ex) {
            throw new HeartbeatFailureException($ex->getMessage());
        }
    }

    private $exchange;
    private $jobQueues;
}
