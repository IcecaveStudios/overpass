<?php

namespace Icecave\Overpass\JobQueue\Job;

/**
 * Represents a job request.
 */
class Job
{
    /**
     * Create a job request.
     *
     * @param string $type    The type of the job to call
     * @param mixed  $payload The payload to pass
     *
     * @return Job
     */
    public static function create($type, $payload)
    {
        return new static($type, $payload);
    }

    /**
     * Get the name of the job to call to complete the job.
     *
     * @return string The name of the job.
     */
    public function type()
    {
        return $this->type;
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
            $this->type,
            json_encode($this->payload)
        );
    }

    /**
     * @param string $type The type of the job to call
     * @param mixed  $payload The payload to pass
     */
    private function __construct($type, $payload)
    {
        $this->type = $type;
        $this->payload = $payload;
    }

    private $type;
    private $payload;
}
