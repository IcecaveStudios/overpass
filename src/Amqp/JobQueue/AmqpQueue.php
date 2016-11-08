<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Icecave\Overpass\JobQueue\QueueInterface;
use Icecave\Overpass\JobQueue\Job\Job;
use Icecave\Overpass\JobQueue\Job\JobSerialization;
use Icecave\Overpass\JobQueue\Job\JobSerializationInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;

class AmqpQueue implements QueueInterface
{
    use LoggerAwareTrait;

    /**
     * @param AMQPChannel                    $channel
     * @param DeclarationManager|null        $declarationManager
     * @param JobSerializationInterface|null $serialization
     */
    public function __construct(
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        JobSerializationInterface $serialization = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JobSerialization(new JsonSerialization());
    }

    /**
     * Add a job to the job queue.
     *
     * @param string $type    The type of job to enqueue
     * @param mixed  $payload The job payload to pass
     */
    public function enqueue($type, $payload = null)
    {
        $job = Job::create($type, $payload);

        if ($this->logger) {
            $this->logger->debug(
                'jobqueue.queue enqueue successful: {job}',
                [
                    'job' => $job,
                ]
            );
        }

        $this->send($job);
    }

    /**
     * Send a job request.
     *
     * @param Job $job
     */
    private function send(Job $job)
    {
        $request = $this
            ->serialization
            ->serializeJob($job);

        $exchange = $this
            ->declarationManager
            ->exchange();

        $this
            ->declarationManager
            ->jobQueue($job->type());

        $this
            ->channel
            ->basic_publish(
                new AMQPMessage($request),
                $exchange,
                $job->type()
            );
    }

    private $channel;
    private $declarationManager;
    private $serialization;
}
