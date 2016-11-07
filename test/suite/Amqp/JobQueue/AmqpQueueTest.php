<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Icecave\Overpass\JobQueue\Job\Job;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;

class AmqpQueueTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->logger = Phake::mock(LoggerInterface::class);

        Phake::when($this->declarationManager)
            ->exchange()
            ->thenReturn('<exchange>');

        Phake::when($this->declarationManager)
            ->jobQueue(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($jobName) {
                    return sprintf('<job-queue-%s>', $jobName);
                }
            );

        $this->subject = new AmqpQueue(
            $this->channel,
            $this->declarationManager
        );
    }

    public function testEnqueue()
    {
        $this->subject->enqueue('job-name', 1);

        $job = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($job),
            '<exchange>',
            'job-name'
        );

        $this->assertEquals(
            new AMQPMessage('["job-name",1]'),
            $job
        );
    }

    public function testEnqueueExtraneousParameters()
    {
        $this->subject->enqueue('job-name', 1, 2, 'bar'); // 2 and 'bar' should be ignored.

        $job = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($job),
            '<exchange>',
            'job-name'
        );

        $this->assertEquals(
            new AMQPMessage('["job-name",1]'),
            $job
        );
    }

    public function testEnqueueWithLogging()
    {
        $this->subject->setLogger($this->logger);
        $this->subject->enqueue('job-name', 1);

        $job = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($job),
            '<exchange>',
            'job-name'
        );

        Phake::verify($this->logger)->debug(
            'jobqueue.queue enqueue successful: {job}',
            [
                'job' => Job::create('job-name', 1),
            ]
        );

        $this->assertEquals(
            new AMQPMessage('["job-name",1]'),
            $job
        );
    }
}
