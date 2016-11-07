<?php

namespace Icecave\Overpass\JobQueue\Job;

use Icecave\Overpass\JobQueue\Exception\InvalidJobException;
use Icecave\Overpass\Serialization\Exception\SerializationException;
use Icecave\Overpass\Serialization\SerializationInterface;

class JobSerialization implements JobSerializationInterface
{
    public function __construct(SerializationInterface $serialization)
    {
        $this->serialization = $serialization;
    }

    /**
     * Serialize a job request.
     *
     * @param Job $job
     *
     * @return string
     */
    public function serializeJob(Job $job)
    {
        $job = [
            $job->type(),
            $job->payload(),
        ];

        return $this
            ->serialization
            ->serialize($job);
    }

    /**
     * Unserialize a job request.
     *
     * @param string $buffer
     *
     * @return Job
     *
     * @throws InvalidJobException
     */
    public function unserializeJob($buffer)
    {
        try {
            $job = $this
                ->serialization
                ->unserialize($buffer);

            if (!is_array($job)) {
                throw new InvalidJobException(
                    'Job request must be a 2-tuple.'
                );
            } elseif (2 !== count($job)) {
                throw new InvalidJobException(
                    'Job request must be a 2-tuple.'
                );
            }

            list($type, $payload) = $job;

            if (!is_string($type)) {
                throw new InvalidJobException(
                    'Job type must be a string.'
                );
            }
        } catch (SerializationException $e) {
            throw new InvalidJobException(
                'Job request is invalid.'
            );
        }

        return Job::create($type, $payload);
    }

    private $serialization;
}
