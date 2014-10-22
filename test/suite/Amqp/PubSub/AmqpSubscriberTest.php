<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\AmqpDeclarationManager;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;

class AmqpSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(AmqpDeclarationManager::class);
        $this->serialization = Phake::mock(SerializationInterface::class);
        $this->message = new AMQPMessage('<payload>');
        $this->message->delivery_info['routing_key'] = 'subscription-topic';
        $this->payload = (object) ['payload' => true];

        Phake::when($this->channel)
            ->basic_consume(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($_, $tag, $_, $_, $_, $_, $callback) {
                    // store the callback as this is used to determine whether to continue waiting
                    $this->channel->callbacks[$tag] = $callback;
                }
            );

        Phake::when($this->channel)
            ->wait()
            ->thenReturn(null)
            ->thenGetReturnByLambda(
                function () {
                    // unset the callback so the consumer stops waiting
                    $this->channel->callbacks = [];
                }
            );

        Phake::when($this->declarationManager)
            ->pubSubExchange(Phake::anyParameters())
            ->thenReturn('<exchange>')
            ->thenThrow(new LogicException('Multiple AMQP exchanges created!'));

        Phake::when($this->declarationManager)
            ->exclusiveQueue(Phake::anyParameters())
            ->thenReturn('<queue>')
            ->thenThrow(new LogicException('Multiple AMQP queues created!'));

        Phake::when($this->serialization)
            ->unserialize('<payload>')
            ->thenReturn($this->payload);

        $this->subscriber = new AmqpSubscriber(
            $this->channel,
            $this->declarationManager,
            $this->serialization
        );
    }

    public function testSubscribe()
    {
        $this->subscriber->subscribe('subscription-topic');

        Phake::inOrder(
            Phake::verify($this->declarationManager)->pubSubExchange($this->channel),
            Phake::verify($this->declarationManager)->exclusiveQueue($this->channel),
            Phake::verify($this->channel)->queue_bind(
                '<queue>',
                '<exchange>',
                'subscription-topic'
            )
        );
    }

    public function testSubscribeToMultipleTopics()
    {
        $this->subscriber->subscribe('subscription-topic-1');
        $this->subscriber->subscribe('subscription-topic-2');

        Phake::inOrder(
            Phake::verify($this->declarationManager)->pubSubExchange($this->channel),
            Phake::verify($this->declarationManager)->exclusiveQueue($this->channel),
            Phake::verify($this->channel)->queue_bind(
                '<queue>',
                '<exchange>',
                'subscription-topic-1'
            ),
            Phake::verify($this->channel)->queue_bind(
                '<queue>',
                '<exchange>',
                'subscription-topic-2'
            )
        );
    }

    public function testSubscribeWithDuplicateSubscription()
    {
        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->subscribe('subscription-topic');

        Phake::verify($this->channel, Phake::times(1))->queue_bind(
            '<queue>',
            '<exchange>',
            'subscription-topic'
        );
    }

    public function testSubscribeWithAtomWildcard()
    {
        $this->subscriber->subscribe('subscription.?.topic');

        Phake::verify($this->channel, Phake::times(1))->queue_bind(
            '<queue>',
            '<exchange>',
            'subscription.*.topic'
        );
    }

    public function testSubscribeWithFullWildcard()
    {
        $this->subscriber->subscribe('subscription.*.topic');

        Phake::verify($this->channel, Phake::times(1))->queue_bind(
            '<queue>',
            '<exchange>',
            'subscription.#.topic'
        );
    }

    public function testUnsubscribe()
    {
        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->unsubscribe('subscription-topic');

        Phake::verify($this->channel)->queue_unbind(
            '<queue>',
            '<exchange>',
            'subscription-topic'
        );
    }

    public function testUnsubscribeWithUnknownSubscription()
    {
        $this->subscriber->unsubscribe('subscription-topic');

        Phake::verifyNoInteraction($this->channel);
        Phake::verifyNoInteraction($this->declarationManager);
    }

    public function testUnsubscribeWithAtomWildcard()
    {
        $this->subscriber->subscribe('subscription.?.topic');
        $this->subscriber->unsubscribe('subscription.?.topic');

        Phake::verify($this->channel)->queue_unbind(
            '<queue>',
            '<exchange>',
            'subscription.*.topic'
        );
    }

    public function testUnsubscribeWithFullWildcard()
    {
        $this->subscriber->unsubscribe('subscription.*.topic');
        $this->subscriber->subscribe('subscription.*.topic');
        $this->subscriber->unsubscribe('subscription.*.topic');

        Phake::verify($this->channel)->queue_unbind(
            '<queue>',
            '<exchange>',
            'subscription.#.topic'
        );
    }

    public function testConsume()
    {
        $calls = [];
        $consumer = function () use (&$calls) {
            $calls[] = func_get_args();

            return true;
        };

        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<queue>',
            'consumer-tag',
            false, // no local
            true,  // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $this->assertTrue(
            is_callable($handler)
        );

        $handler($this->message);

        $this->assertSame(
            [['subscription-topic', $this->payload]],
            $calls
        );

        Phake::verify($this->channel, Phake::times(2))->wait();
        Phake::verify($this->channel, Phake::never())->basic_cancel(Phake::anyParameters());
    }

    public function testConsumeCancel()
    {
        $consumer = function () {
            return false;
        };

        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<queue>',
            'consumer-tag',
            false, // no local
            true,  // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        Phake::verify($this->channel, Phake::times(2))->wait();

        $this->assertTrue(
            is_callable($handler)
        );

        $handler($this->message);

        Phake::verify($this->channel)->basic_cancel('consumer-tag');
    }

    public function testConsumeWithNoSubscriptions()
    {
        $consumer = function () {
            throw new LogicException('Consumer should not be invoked.');
        };

        $this->subscriber->consume($consumer);

        Phake::verifyNoInteraction($this->channel);
    }
}
