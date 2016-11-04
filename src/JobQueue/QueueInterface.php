<?php

namespace Icecave\Overpass\JobQueue;

use Psr\Log\LoggerAwareInterface;

interface QueueInterface extends LoggerAwareInterface
{
    /**
     * Add a job to the job queue.
     *
     * @param string $type    The type of job to enqueue
     * @param mixed  $payload The job payload to pass
     */
    public function enqueue($type, $payload = null);
}
