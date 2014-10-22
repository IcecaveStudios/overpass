<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit_Framework_TestCase;

class AmqpDeclarationManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);

        Phake::when($this->channel)
            ->queue_declare(Phake::anyParameters())
            ->thenReturn(['<queue>', 0, 0]);

        $this->declarationManager = new AmqpDeclarationManager();
    }

    public function testPubSubExchange()
    {
        $name = $this->declarationManager->pubSubExchange($this->channel);

        Phake::verify($this->channel)->exchange_declare(
            'overpass.pubsub',
            'topic',
            false, // passive,
            false, // durable,
            false  // auto delete
        );

        $this->assertSame(
            'overpass.pubsub',
            $name
        );
    }

    public function testExclusiveQueue()
    {
        $name = $this->declarationManager->exclusiveQueue($this->channel);

        Phake::verify($this->channel)->queue_declare(
            '',    // name
            false, // passive
            false, // durable,
            true   // exclusive
        );

        $this->assertSame(
            '<queue>',
            $name
        );
    }

    public function testRpcQueue()
    {
        $name = $this->declarationManager->rpcQueue($this->channel, 'foo.bar');

        Phake::verify($this->channel)->queue_declare(
            'overpass.rpc.foo.bar'
        );

        $this->assertSame(
            '<queue>',
            $name
        );
    }
}
