<?php

namespace Icecave\Overpass\JobQueue\Job;

use Icecave\Overpass\JobQueue\Exception\InvalidJobException;

interface JobSerializationInterface
{
    /**
     * Serialize a job request.
     *
     * @param Job $job
     *
     * @return string
     */
    public function serializeJob(Job $job);

    /**
     * Unserialize a job request.
     *
     * @param string $buffer
     *
     * @return Job
     *
     * @throws InvalidJobException
     */
    public function unserializeJob($buffer);
}
