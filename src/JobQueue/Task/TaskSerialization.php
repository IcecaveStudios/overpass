<?php

namespace Icecave\Overpass\JobQueue\Task;

use Icecave\Overpass\JobQueue\Exception\InvalidTaskException;
use Icecave\Overpass\Serialization\Exception\SerializationException;
use Icecave\Overpass\Serialization\SerializationInterface;

class TaskSerialization implements TaskSerializationInterface
{
    public function __construct(SerializationInterface $serialization)
    {
        $this->serialization = $serialization;
    }

    /**
     * Serialize a task request.
     *
     * @param Task $task
     *
     * @return string
     */
    public function serializeTask(Task $task)
    {
        $task = [
            $task->jobName(),
            $task->payload(),
        ];

        return $this
            ->serialization
            ->serialize($task);
    }

    /**
     * Unserialize a task request.
     *
     * @param string $buffer
     *
     * @return Task
     *
     * @throws InvalidTaskException
     */
    public function unserializeTask($buffer)
    {
        try {
            $task = $this
                ->serialization
                ->unserialize($buffer);

            if (!is_array($task)) {
                throw new InvalidTaskException(
                    'Task request must be a 2-tuple.'
                );
            } elseif (2 !== count($task)) {
                throw new InvalidTaskException(
                    'Task request must be a 2-tuple.'
                );
            }

            list($jobName, $payload) = $task;

            if (!is_string($jobName)) {
                throw new InvalidTaskException(
                    'Job name must be a string.'
                );
            }
        } catch (SerializationException $e) {
            throw new InvalidTaskException(
                'Task request is invalid.'
            );
        }

        return Task::create($jobName, $payload);
    }

    private $serialization;
}
