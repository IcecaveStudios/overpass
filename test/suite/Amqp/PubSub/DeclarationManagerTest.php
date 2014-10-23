<?php
namespace Icecave\Overpass\Amqp\PubSub;

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
            ->thenReturn(['<queue>', 0, 0]);

        $this->declarationManager = new DeclarationManager($this->channel);
    }

    public function testExchange()
    {
        $name = $this->declarationManager->exchange();

        Phake::verify($this->channel)->exchange_declare(
            'overpass/pubsub',
            'topic',
            false, // passive,
            false, // durable,
            false  // auto delete
        );

        $this->assertSame(
            'overpass/pubsub',
            $name
        );
    }

    public function testExchangeDeclaresOnce()
    {
        $this->declarationManager->exchange();

        $this->assertSame(
            'overpass/pubsub',
            $this->declarationManager->exchange()
        );

        Phake::verify($this->channel, Phake::times(1))->exchange_declare(
            Phake::anyParameters()
        );
    }

    public function testQueue()
    {
        $name = $this->declarationManager->queue();

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

    public function testQueueDeclaresOnce()
    {
        $this->declarationManager->queue();

        $this->assertSame(
            '<queue>',
            $this->declarationManager->queue()
        );

        Phake::verify($this->channel)->queue_declare(
            '',    // name
            false, // passive
            false, // durable,
            true   // exclusive
        );
    }
}
