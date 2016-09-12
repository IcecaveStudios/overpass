<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Icecave\Overpass\JobQueue\QueueInterface;
use Icecave\Overpass\JobQueue\Task\Task;
use Icecave\Overpass\JobQueue\Task\TaskSerialization;
use Icecave\Overpass\JobQueue\Task\TaskSerializationInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;

class AmqpQueue implements QueueInterface
{
    use LoggerAwareTrait;

    /**
     * @param AMQPChannel                     $channel
     * @param DeclarationManager|null         $declarationManager
     * @param TaskSerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        TaskSerializationInterface $serialization = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new TaskSerialization(new JsonSerialization());
    }

    /**
     * Add a task to the job queue.
     *
     * @param string $task    The name of the task to enqueue
     * @param mixed  $payload The task payload to pass
     */
    public function enqueue($task, $payload = null)
    {
        $task = Task::create($task, $payload);

        if ($this->logger) {
            $this->logger->debug(
                'jobqueue.queue enqueue successful: {task}',
                [
                    'task' => $task,
                ]
            );
        }

        $this->send($task);
    }

    /**
     * Send a task request.
     *
     * @param Task $task
     */
    private function send(Task $task)
    {
        $request = $this
            ->serialization
            ->serializeTask($task);

        $exchange = $this
            ->declarationManager
            ->exchange();

        $this
            ->declarationManager
            ->jobQueue($task->jobName());

        $this
            ->channel
            ->basic_publish(
                new AMQPMessage($request),
                $exchange,
                $task->jobName()
            );
    }

    private $channel;
    private $declarationManager;
    private $serialization;
}
