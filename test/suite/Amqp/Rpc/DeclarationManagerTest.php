<?php
namespace Icecave\Overpass\Amqp\Rpc;

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
        $name = $this->declarationManager->requestQueue('procedure-1');

        Phake::verify($this->channel)->queue_declare(
            'overpass/rpc/procedure-1',
            false, // passive
            false, // durable
            false, // exclusive
            false  // auto delete
        );

        $this->assertSame(
            'overpass/rpc/procedure-1',
            $name
        );

        $name = $this->declarationManager->requestQueue('procedure-2');

        Phake::verify($this->channel)->queue_declare(
            'overpass/rpc/procedure-2',
            false, // passive
            false, // durable
            false, // exclusive
            false  // auto delete
        );

        $this->assertSame(
            'overpass/rpc/procedure-2',
            $name
        );
    }

    public function testRequestQueueDeclaresOnce()
    {
        $this->declarationManager->requestQueue('procedure-name');

        $this->assertSame(
            'overpass/rpc/procedure-name',
            $this->declarationManager->requestQueue('procedure-name')
        );

        Phake::verify($this->channel, Phake::times(1))->queue_declare(
            Phake::anyParameters()
        );
    }

    public function testResponseQueue()
    {
        $name = $this->declarationManager->responseQueue();

        Phake::verify($this->channel)->queue_declare(
            '',
            false, // passive
            false, // durable
            true,  // exclusive
            true   // auto delete
        );

        $this->assertSame(
            '<queue>',
            $name
        );
    }

    public function testResponseQueueDeclaresOnce()
    {
        $this->declarationManager->responseQueue();

        $this->assertSame(
            '<queue>',
            $this->declarationManager->responseQueue()
        );

        Phake::verify($this->channel, Phake::times(1))->queue_declare(
            Phake::anyParameters()
        );
    }
}
