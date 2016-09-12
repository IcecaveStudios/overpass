<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Eloquent\Asplode\Error\ErrorException;
use Error;
use Exception;
use Icecave\Overpass\Amqp\ChannelDispatcher;
use Icecave\Overpass\JobQueue\Exception\DiscardException;
use Icecave\Overpass\JobQueue\Exception\InvalidTaskException;
use Icecave\Overpass\JobQueue\Request;
use Icecave\Overpass\JobQueue\Task\TaskSerialization;
use Icecave\Overpass\JobQueue\Task\TaskSerializationInterface;
use Icecave\Overpass\JobQueue\WorkerInterface;
use Icecave\Overpass\Serialization\JsonSerialization;
use Icecave\Overpass\Serialization\SerializationInterface;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use ReflectionClass;

class AmqpWorker implements WorkerInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface             $logger
     * @param AMQPChannel                 $channel
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     * @param ChannelDispatcher           $channelDispatcher
     */
    public function __construct(
        LoggerInterface $logger,
        AMQPChannel $channel,
        DeclarationManager $declarationManager = null,
        TaskSerializationInterface $serialization = null,
        ChannelDispatcher $channelDispatcher = null
    ) {
        $this->channel = $channel;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new TaskSerialization(new JsonSerialization());
        $this->channelDispatcher = $channelDispatcher ?: new ChannelDispatcher();
        $this->jobs = [];
        $this->consumerTags = [];

        $this->setLogger($logger);
    }

    /**
     * Register a job.
     *
     * @param string   $jobName The public name of the job.
     * @param callable $handler The handler to be registered against the job.
     *
     * @throws LogicException if the worker is already running.
     */
    public function register($jobName, callable $handler)
    {
        if ($this->channel->callbacks) {
            throw new LogicException(
                'Jobs can not be registered while the worker is running.'
            );
        }

        $this->jobs[$jobName] = $handler;
    }

    /**
     * Register all public methods on an object against jobs of the same name.
     *
     * @param object $object The object with the methods to register.
     * @param string $prefix A string to prefix all the method names with.
     */
    public function registerObject($object, $prefix = '')
    {
        $reflector = new ReflectionClass($object);

        foreach ($reflector->getMethods() as $method) {
            if ($method->isStatic()) {
                continue;
            } elseif (!$method->isPublic()) {
                continue;
            } elseif ('_' === $method->getName()[0]) {
                continue;
            }

            $jobName = $prefix . $method->getName();

            $this->register(
                $jobName,
                [$object, $method->getName()]
            );
        }
    }

    /**
     * Run the worker.
     */
    public function run()
    {
        $this->isStopping = false;

        // Bind queues / consumers ...
        foreach ($this->jobs as $jobName => $job) {
            $this->bind($jobName);

            $this->logger->debug(
                'jobqueue.worker registered job "{job}"',
                ['job' => $jobName]
            );
        }

        if ($this->jobs) {
            $this->logger->info('jobqueue.worker started successfully');
        } else {
            $this->logger->warning('jobqueue.worker started without registered jobs');
        }

        while ($this->channel->callbacks) {
            $this->channelDispatcher->wait($this->channel);

            if ($this->isStopping) {
                foreach ($this->jobs as $jobName => $job) {
                    $this->unbind($jobName);
                }
            }
        }

        $this->logger->info('jobqueue.worker shutdown gracefully');
    }

    /**
     * Stop the worker.
     */
    public function stop()
    {
        if (!$this->isStopping) {
            $this->isStopping = true;

            $this->logger->info('jobqueue.worker stopping');
        }
    }

    /**
     * Receive a task request message.
     *
     * @param AMQPMessage $message
     */
    private function recv(AMQPMessage $message)
    {
        $logLevel = LogLevel::DEBUG;
        $logContext = [
            'task' => '<unknown>',
            'payload' => '<unknown>',
        ];

        $logMessage = null;
        $logCode = 0;

        try {
            $task = $this->serialization->unserializeTask($message->body);

            $logContext['task'] = $task->jobName();
            $logContext['payload'] = json_encode($task->payload());

            call_user_func(
                $this->jobs[$task->jobName()],
                $task->payload()
            );

            $logMessage = 'jobqueue.worker completed task {task}({payload})';
            $this->channel->basic_ack($message->get('delivery_tag'));
        } catch (InvalidTaskException $e) {
            $logLevel = LogLevel::WARNING;
            $logMessage = $this->handleFailure($message, $e, $logContext, true);    // discard
        } catch (DiscardException $e) {
            $logLevel = LogLevel::ERROR;
            $logMessage = $this->handleFailure($message, $e, $logContext, true);    // discard
        } catch (ErrorException $e) {
            $logLevel = LogLevel::ERROR;
            $logMessage = $this->handleFailure($message, $e, $logContext);
        } catch (Exception $e) {
            $logLevel = LogLevel::ERROR;
            $logContext['exception'] = $e;
            $logMessage = $this->handleFailure(
                $message,
                new Exception('Internal server error.', $e->getCode()),
                $logContext
            );
        } catch (Error $e) {
            $logLevel = LogLevel::ERROR;
            $e = new Exception('Internal server error.', $e->getCode());
            $logContext['exception'] = $e;
            $logMessage = $this->handleFailure($message, $e, $logContext);
        }

        $this->logger->log($logLevel, $logMessage, $logContext);
    }

    /**
     * @param Exception $exception The error or exception being handled.
     */
    private function handleFailure(
        AMQPMessage $message,
        Exception $exception,
        &$logContext,
        $discard = false
    ) {
        $logContext['code'] = $exception->getCode();
        $logContext['reason'] = json_encode($exception->getMessage());

        if ($discard) {
            $this->channel->basic_reject($message->get('delivery_tag'), false);

            return 'jobqueue.worker discarding failed task {task}({payload}) -> {code} {reason}';
        }

        // message failed even after being redelivered so sleep before redelivering
        if ($message->get('redelivered')) {
            usleep(250000);
        }

        $this->channel->basic_reject($message->get('delivery_tag'), true);

        return 'jobqueue.worker requeuing failed task {task}({payload}) -> {code} {reason}';
    }

    private function bind($jobName)
    {
        $queue = $this
            ->declarationManager
            ->jobQueue($jobName);

        $this->consumerTags[$jobName] = $this
            ->channel
            ->basic_consume(
                $queue,
                '',    // consumer tag
                false, // no local
                false, // no ack
                false, // exclusive
                false, // no wait
                $handler = function ($message) {
                    $this->recv($message);
                }
            );
    }

    private function unbind($jobName)
    {
        $this
            ->channel
            ->basic_cancel(
                $this->consumerTags[$jobName]
            );

        unset($this->consumerTags[$jobName]);
    }

    private $channel;
    private $declarationManager;
    private $serialization;
    private $channelDispatcher;
    private $isStopping;
    private $jobs;
    private $consumerTags;
}
