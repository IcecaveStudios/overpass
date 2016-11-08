<?php
namespace Icecave\Overpass\Amqp\PubSub;

use Icecave\Overpass\Amqp\ChannelDispatcher;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use PHPUnit_Framework_TestCase;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class AmqpSubscriberTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel                               = Phake::mock(AMQPChannel::class);
        $this->declarationManager                    = Phake::mock(DeclarationManager::class);
        $this->serialization                         = Phake::mock(SerializationInterface::class);
        $this->channelDispatcher                     = Phake::mock(ChannelDispatcher::class);
        $this->logger                                = Phake::mock(LoggerInterface::class);
        $this->message                               = new AMQPMessage('<payload>');
        $this->message->delivery_info['routing_key'] = 'subscription-topic';
        $this->payload                               = (object) ['payload' => true];

        Phake::when($this->channel)
            ->basic_consume(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($a, $tag, $b, $c, $d, $e, $callback) {

                    if ($tag === '') {
                        $tag = '<consumer-tag>';
                    }

                    // store the callback as this is used to determine whether to continue waiting
                    $this->channel->callbacks[$tag] = $callback;

                    return $tag;
                }
            );

        Phake::when($this->channelDispatcher)
            ->wait($this->channel)
            ->thenReturn(null)
            ->thenGetReturnByLambda(
                function () {
                    // unset the callback so the consumer stops waiting
                    $this->channel->callbacks = [];
                }
            );

        Phake::when($this->declarationManager)
            ->exchange()
            ->thenReturn('<exchange>');

        Phake::when($this->declarationManager)
            ->queue()
            ->thenReturn('<queue>');

        Phake::when($this->serialization)
            ->unserialize('<payload>')
            ->thenReturn($this->payload);

        $this->subscriber = new AmqpSubscriber(
            $this->channel,
            $this->declarationManager,
            $this->serialization,
            $this->channelDispatcher
        );
    }

    public function testSubscribe()
    {
        $this->subscriber->subscribe('subscription-topic');

        Phake::inOrder(
            Phake::verify($this->declarationManager)->queue(),
            Phake::verify($this->declarationManager)->exchange(),
            Phake::verify($this->channel)->queue_bind(
                '<queue>',
                '<exchange>',
                'subscription-topic'
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

    public function testSubscribeLogging()
    {
        $this->subscriber->setLogger($this->logger);

        $this->subscriber->subscribe('subscription.?.topic');

        Phake::verify($this->logger)->debug(
            'pubsub.subscriber {topic} subscribe',
            [
                'topic' => 'subscription.?.topic',
            ]
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

    public function testUnsubscribeLogging()
    {
        $this->subscriber->setLogger($this->logger);

        $this->subscriber->subscribe('subscription.?.topic');
        $this->subscriber->unsubscribe('subscription.?.topic');

        Phake::verify($this->logger)->debug(
            'pubsub.subscriber {topic} unsubscribe',
            [
                'topic' => 'subscription.?.topic',
            ]
        );
    }

    public function testConsume()
    {
        $calls    = [];
        $consumer = function () use (&$calls) {
            $calls[] = func_get_args();

            return true;
        };

        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<queue>',
            '',
            false, // no local
            true,  // no ack
            true,  // exclusive
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

        Phake::verify($this->channelDispatcher, Phake::times(2))->wait($this->channel);
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
            '',
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        Phake::verify($this->channelDispatcher, Phake::times(2))->wait($this->channel);

        $this->assertTrue(
            is_callable($handler)
        );

        $handler($this->message);

        Phake::verify($this->channel)->basic_cancel('<consumer-tag>');
    }

    public function testConsumeWithNoSubscriptions()
    {
        $consumer = function () {
            throw new LogicException('Consumer should not be invoked.');
        };

        $this->subscriber->consume($consumer);

        Phake::verifyNoInteraction($this->channel);
    }

    public function testConsumeLogging()
    {
        $this->subscriber->setLogger($this->logger);

        $consumer = function () {
            return false;
        };

        $this->subscriber->subscribe('subscription-topic');
        $this->subscriber->consume($consumer);

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<queue>',
            '',
            false, // no local
            true,  // no ack
            true,  // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $handler($this->message);

        Phake::verify($this->logger)->debug(
            'pubsub.subscriber {topic} receive: {payload}',
            [
                'topic'   => 'subscription-topic',
                'payload' => json_encode($this->payload),
            ]
        );
    }
}
