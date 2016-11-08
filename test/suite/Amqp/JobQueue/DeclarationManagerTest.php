<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit_Framework_TestCase;

class DeclarationManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);

        Phake::when($this->channel)
            ->queue_declare(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($name) {
                    if ($name === '') {
                        $name = '<queue>';
                    }

                    return [$name, 0, 0];
                }
            );

        $this->declarationManager = new DeclarationManager($this->channel);
    }

    public function testRequestQueue()
    {
        $this->declarationManager->jobQueue('job-1');

        Phake::verify($this->channel)->queue_declare(
            'overpass/job/job-1',
            false, // passive
            false, // durable
            false, // exclusive
            false  // auto delete
        );

        $this->declarationManager->jobQueue('job-2');

        Phake::verify($this->channel)->queue_declare(
            'overpass/job/job-2',
            false, // passive
            false, // durable
            false, // exclusive
            false  // auto delete
        );
    }

    public function testRequestQueueDeclaresOnce()
    {
        $this->declarationManager->jobQueue('job-name');

        $this->assertSame(
            'overpass/job/job-name',
            $this->declarationManager->jobQueue('job-name')
        );

        Phake::verify($this->channel, Phake::times(1))->queue_declare(
            Phake::anyParameters()
        );
    }
}
