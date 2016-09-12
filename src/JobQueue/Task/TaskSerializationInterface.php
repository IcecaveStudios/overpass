<?php

namespace Icecave\Overpass\JobQueue\Task;

use Icecave\Overpass\JobQueue\Exception\InvalidTaskException;

interface TaskSerializationInterface
{
    /**
     * Serialize a task request.
     *
     * @param Task $task
     *
     * @return string
     */
    public function serializeTask(Task $task);

    /**
     * Unserialize a task request.
     *
     * @param string $buffer
     *
     * @return Task
     *
     * @throws InvalidTaskException
     */
    public function unserializeTask($buffer);
}
