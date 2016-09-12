<?php

namespace Icecave\Overpass\JobQueue\Task;

/**
 * Represents a task request.
 */
class Task
{
    /**
     * Create a task request.
     *
     * @param string $jobName The jobName of the job to call
     * @param mixed  $payload The payload to pass
     *
     * @return Task
     */
    public static function create($jobName, $payload)
    {
        return new static($jobName, $payload);
    }

    /**
     * Get the name of the job to call to complete the task.
     *
     * @return string The name of the job.
     */
    public function jobName()
    {
        return $this->jobName;
    }

    /**
     * Get the payload to pass.
     *
     * @return array The payload to pass
     */
    public function payload()
    {
        return $this->payload;
    }

    public function __toString()
    {
        return sprintf(
            '%s(%s)',
            $this->jobName,
            json_encode($this->payload)
        );
    }

    /**
     * @param string $jobName The jobName of the job to call
     * @param mixed  $payload The payload to pass
     */
    private function __construct($jobName, $payload)
    {
        $this->jobName = $jobName;
        $this->payload = $payload;
    }

    private $jobName;
    private $payload;
}
