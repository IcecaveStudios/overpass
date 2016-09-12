<?php

namespace Icecave\Overpass\JobQueue;

use Psr\Log\LoggerAwareInterface;

interface QueueInterface extends LoggerAwareInterface
{
    /**
     * Add a task to the job queue.
     *
     * @param string $task    The name of the task to enqueue
     * @param mixed  $payload The task payload to pass
     */
    public function enqueue($task, $payload);
}
