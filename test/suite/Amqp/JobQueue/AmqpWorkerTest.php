<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Eloquent\Asplode\Error\ErrorException;
use Exception;
use Icecave\Overpass\Amqp\ChannelDispatcher;
use Icecave\Overpass\JobQueue\Exception\DiscardException;
use Icecave\Overpass\JobQueue\Job\Job;
use LogicException;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit_Framework_TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class AmqpWorkerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel            = Phake::mock(AMQPChannel::class);
        $this->declarationManager = Phake::mock(DeclarationManager::class);
        $this->logger             = Phake::mock(LoggerInterface::class);
        $this->channelDispatcher  = Phake::mock(ChannelDispatcher::class);
        $this->job1 = function () {
            return '<job-1: ' . implode(', ', array_map('json_encode', func_get_args())) . '>';
        };
        $this->job2 = function () {
            return '<job-2: ' . implode(', ', array_map('json_encode', func_get_args())) . '>';
        };
        $this->consumerTagCounter = 0;

        Phake::when($this->channel)
            ->basic_consume(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($a, $tag, $b, $c, $d, $e, $callback) {
                    if ($tag === '') {
                        $tag = '<consumer-tag-' . ++$this->consumerTagCounter . '>';
                    }

                    // store the callback as this is used to determine whether to continue waiting
                    $this->channel->callbacks[$tag] = $callback;

                    return $tag;
                }
            );

        Phake::when($this->channel)
            ->basic_cancel(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($tag) {
                    unset($this->channel->callbacks[$tag]);
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
            ->jobQueue(Phake::anyParameters())
            ->thenGetReturnByLambda(
                function ($name) {
                    return sprintf('<job-queue-%s>', $name);
                }
            );

        $this->worker = Phake::partialMock(
            AmqpWorker::class,
            $this->logger,
            $this->channel,
            $this->declarationManager,
            null,
            $this->channelDispatcher
        );
    }

    public function testRegiserWhileRunning()
    {
        Phake::when($this->channelDispatcher)
            ->wait($this->channel)
            ->thenGetReturnByLambda(
                function () {
                    $this->worker->register('job-2', $this->job2);
                }
            )->thenGetReturnByLambda(
                function () {
                    // unset the callback so the consumer stops waiting
                    $this->channel->callbacks = [];
                }
            );

        $this->worker->register('job-1', $this->job1);

        $this->setExpectedException(
            LogicException::class,
            'Handlers can not be registered while the worker is running.'
        );

        $this->worker->run();

        $this->worker->register('job-2', $this->job2);
    }

    public function testRegisterObject()
    {
        $object = new RegisteredObject();

        $this->worker->registerObject($object);

        Phake::verify($this->worker)->register(
            'methodOne',
            [$object, 'methodOne']
        );

        Phake::verify($this->worker)->register(
            'methodTwo',
            [$object, 'methodTwo']
        );

        Phake::verify($this->worker, Phake::never())->register(
            '__construct',
            Phake::anyParameters()
        );

        Phake::verify($this->worker, Phake::never())->register(
            '__destruct',
            Phake::anyParameters()
        );

        Phake::verify($this->worker, Phake::never())->register(
            '__toString',
            Phake::anyParameters()
        );

        Phake::verify($this->worker, Phake::never())->register(
            'privateMethod',
            Phake::anyParameters()
        );

        Phake::verify($this->worker, Phake::never())->register(
            'staticMethod',
            Phake::anyParameters()
        );
    }

    public function testRegiserObjectWithPrefix()
    {
        $object = new RegisteredObject();

        $this->worker->registerObject($object, 'foo.');

        Phake::verify($this->worker)->register(
            'foo.methodOne',
            [$object, 'methodOne']
        );

        Phake::verify($this->worker)->register(
            'foo.methodTwo',
            [$object, 'methodTwo']
        );
    }

    public function testRun()
    {
        $this->worker->register('job-1', $this->job1);
        $this->worker->register('job-2', $this->job2);

        $this->worker->run();

        $handler = null;

        Phake::inOrder(
            Phake::verify($this->channel)->basic_consume(
                '<job-queue-job-1>',
                '',    // consumer tag
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                Phake::capture($handler)
            ),
            Phake::verify($this->logger)->debug(
                'jobqueue.worker registered handler for type "{type}"',
                ['type' => 'job-1']
            ),
            Phake::verify($this->channel)->basic_consume(
                '<job-queue-job-2>',
                '',    // consumer tag
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                $handler
            ),
            Phake::verify($this->logger)->debug(
                'jobqueue.worker registered handler for type "{type}"',
                ['type' => 'job-2']
            ),
            Phake::verify($this->logger)->info('jobqueue.worker started successfully'),
            Phake::verify($this->channelDispatcher, Phake::times(2))->wait($this->channel),
            Phake::verify($this->logger)->info('jobqueue.worker shutdown gracefully')
        );

        $this->assertTrue(
            is_callable($handler)
        );
    }

    public function testRunNoProcedures()
    {
        $this->worker->run();

        Phake::inOrder(
            Phake::verify($this->logger)->warning('jobqueue.worker started without registered handlers'),
            Phake::verify($this->logger)->info('jobqueue.worker shutdown gracefully')
        );

        Phake::verifyNoInteraction($this->channel);
    }

    public function testStop()
    {
        Phake::when($this->channelDispatcher)
            ->wait($this->channel)
            ->thenGetReturnByLambda(
                function () {
                    $this->worker->stop();
                }
            );

        $this->worker->register('job-1', $this->job1);
        $this->worker->register('job-2', $this->job2);

        $this->worker->run();

        Phake::verify($this->logger)->info('jobqueue.worker stopping');
        Phake::verify($this->channel)->basic_cancel('<consumer-tag-1>');
        Phake::verify($this->channel)->basic_cancel('<consumer-tag-2>');
    }

    public function testReceiveRequest()
    {
        $this->worker->register('job-type', $this->job1);

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('["job-type",[1,{"a":2,"b":3}]]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = false;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_ack('<delivery-tag>');
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::DEBUG,
            'jobqueue.worker completed job {job}({payload})',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'type' => 'job-type',
                'payload' => '[1,{"a":2,"b":3}]',
            ],
            $context
        );
    }

    public function testReceiveRequestWithInvalidRequest()
    {
        $this->worker->register(
            'job-type',
            $this->job1
        );

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('[null]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = false;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', false);
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::WARNING,
            'jobqueue.worker discarding failed job {job}({payload}) -> {code} {reason}',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'code' => 0,
                'reason' => '"Job request must be a 2-tuple."',
                'type' => '<unknown>',
                'payload' => '<unknown>',
            ],
            $context
        );
    }

    public function testReceiveRequestWithDiscardException()
    {
        $this->worker->register(
            'job-type',
            function () {
                throw new DiscardException('Job no longer needed. please discard.');
            }
        );

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('["job-type",[1,{"a":2,"b":3}]]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = false;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', false);
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::ERROR,
            'jobqueue.worker discarding failed job {job}({payload}) -> {code} {reason}',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'code' => 0,
                'reason' => '"Job no longer needed. please discard."',
                'type' => 'job-type',
                'payload' => '[1,{"a":2,"b":3}]',
            ],
            $context
        );
    }

    public function testReceiveRequestWithErrorException()
    {
        $this->worker->register(
            'job-type',
            function () {
                throw new ErrorException(
                    'Things gone done broked.', // message
                    0,                          // severity
                    '/',                        // path
                    100                         // line number
                );
            }
        );

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('["job-type",[1,{"a":2,"b":3}]]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = true;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', true);
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::ERROR,
            'jobqueue.worker requeuing failed job {job}({payload}) -> {code} {reason}',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'code' => 0,
                'reason' => '"Things gone done broked."',
                'type' => 'job-type',
                'payload' => '[1,{"a":2,"b":3}]',
            ],
            $context
        );
    }

    public function testReceiveRequestWithGenericException()
    {
        $exception = new Exception('Things gone done broked.', 123);
        $this->worker->register(
            'job-type',
            function () use ($exception) {
                throw $exception;
            }
        );

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('["job-type",[1,{"a":2,"b":3}]]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = true;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', true);
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::ERROR,
            'jobqueue.worker requeuing failed job {job}({payload}) -> {code} {reason}',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'code' => 123,
                'reason' => '"Internal server error."',
                'type' => 'job-type',
                'payload' => '[1,{"a":2,"b":3}]',
                'exception' => $exception,
            ],
            $context
        );
    }

    /**
     * @requires PHP 7
     */
    public function testReceiveRequestWithError()
    {
        $exception = new Exception('Internal server error.', 0);
        $this->worker->register(
            'job-type',
            function (int $foo) { // will cause TypeError in php7 and asplode ErrorException in php5 when we invoke with an object
                return $foo;
            }
        );

        $this->worker->run();

        $handler = null;

        Phake::verify($this->channel)->basic_consume(
            '<job-queue-job-type>',
            '',    // consumer tag
            false, // no local
            false, // no ack
            false, // exclusive
            false, // no wait
            Phake::capture($handler)
        );

        $jobRequest = new AMQPMessage('["job-type",[1,{"a":2,"b":3}]]', []);
        $jobRequest->delivery_info['delivery_tag'] = '<delivery-tag>';
        $jobRequest->delivery_info['redelivered'] = true;

        $handler($jobRequest);

        Phake::verify($this->channel)->basic_reject('<delivery-tag>', true);
        $context = null;

        Phake::verify($this->logger)->log(
            LogLevel::ERROR,
            'jobqueue.worker requeuing failed job {job}({payload}) -> {code} {reason}',
            Phake::capture($context)
        );

        $this->assertEquals(
            [
                'code' => 0,
                'type' => 'job-type',
                'payload' => '[1,{"a":2,"b":3}]',
                'reason' => '"Internal server error."',
                'exception' => $exception,
            ],
            $context
        );
    }
}
