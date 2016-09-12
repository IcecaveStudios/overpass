<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Icecave\Overpass\JobQueue\Task\Task;
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
        $this->subject->enqueue('task-name', 1);

        $task = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($task),
            '<exchange>',
            'task-name'
        );

        $this->assertEquals(
            new AMQPMessage('["task-name",1]'),
            $task
        );
    }

    public function testEnqueueExtraneousParameters()
    {
        $this->subject->enqueue('task-name', 1, 2, 'bar'); // 2 and 'bar' should be ignored.

        $task = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($task),
            '<exchange>',
            'task-name'
        );

        $this->assertEquals(
            new AMQPMessage('["task-name",1]'),
            $task
        );
    }

    public function testEnqueueWithLogging()
    {
        $this->subject->setLogger($this->logger);
        $this->subject->enqueue('task-name', 1);

        $task = null;

        Phake::verify($this->channel)->basic_publish(
            Phake::capture($task),
            '<exchange>',
            'task-name'
        );

        Phake::verify($this->logger)->debug(
            'jobqueue.queue enqueue successful: {task}',
            [
                'task' => Task::create('task-name', 1),
            ]
        );

        $this->assertEquals(
            new AMQPMessage('["task-name",1]'),
            $task
        );
    }
}
