<?php

namespace Icecave\Overpass\Amqp\JobQueue;

use Eloquent\Asplode\Error\ErrorException;
use Exception;
use Icecave\Overpass\Amqp\ChannelDispatcher;
use Icecave\Overpass\JobQueue\Exception\DiscardException;
use Icecave\Overpass\JobQueue\Exception\InvalidJobException;
use Icecave\Overpass\JobQueue\Job\JobSerialization;
use Icecave\Overpass\JobQueue\Job\JobSerializationInterface;
use Icecave\Overpass\JobQueue\Request;
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
use Throwable;

class AmqpWorker implements WorkerInterface
{
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface             $logger
     * @param AMQPChannel                 $channel
     * @param callable|null               $errorHandler
     * @param DeclarationManager|null     $declarationManager
     * @param SerializationInterface|null $serialization
     * @param ChannelDispatcher           $channelDispatcher
     */
    public function __construct(
        LoggerInterface $logger,
        AMQPChannel $channel,
        callable $errorHandler = null,
        DeclarationManager $declarationManager = null,
        JobSerializationInterface $serialization = null,
        ChannelDispatcher $channelDispatcher = null
    ) {
        $this->channel = $channel;
        $this->errorHandler       = $errorHandler;
        $this->declarationManager = $declarationManager ?: new DeclarationManager($channel);
        $this->serialization = $serialization ?: new JobSerialization(new JsonSerialization());
        $this->channelDispatcher = $channelDispatcher ?: new ChannelDispatcher();
        $this->handlers = [];
        $this->consumerTags = [];

        $this->setLogger($logger);
    }

    /**
     * Register a job.
     *
     * @param string   $type    The public name of the job.
     * @param callable $handler The handler to be registered against the job.
     *
     * @throws LogicException if the worker is already running.
     */
    public function register($type, callable $handler)
    {
        if ($this->channel->callbacks) {
            throw new LogicException(
                'Handlers can not be registered while the worker is running.'
            );
        }

        $this->handlers[$type] = $handler;
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

            $type = $prefix . $method->getName();

            $this->register(
                $type,
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
        foreach ($this->handlers as $type => $handler) {
            $this->bind($type);

            $this->logger->debug(
                'jobqueue.worker registered handler for type "{type}"',
                ['type' => $type]
            );
        }

        if ($this->handlers) {
            $this->logger->info('jobqueue.worker started successfully');
        } else {
            $this->logger->warning('jobqueue.worker started without registered handlers');
        }

        while ($this->channel->callbacks) {
            $this->channelDispatcher->wait($this->channel);

            if ($this->isStopping) {
                foreach ($this->handlers as $type => $handler) {
                    $this->unbind($type);
                }
            }
        }

        if ($this->uncaughtThrowable !== null) {
            $this->logger->critical('jobqueue.worker shutdown due to uncaught exception');
            throw $this->uncaughtThrowable;
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
     * Receive a job request message.
     *
     * @param AMQPMessage $message
     */
    private function recv(AMQPMessage $message)
    {
        $logLevel = LogLevel::INFO;
        $logContext = [
            'type' => '<unknown>',
            'payload' => '<unknown>',
        ];

        $logMessage = null;
        $logCode = 0;

        try {
            $job = $this->serialization->unserializeJob($message->body);

            $logContext['type'] = $job->type();
            $logContext['payload'] = json_encode($job->payload());

            $this->logger->debug(
                'jobqueue.worker started job {type}({payload})',
                $logContext
            );

            call_user_func(
                $this->handlers[$job->type()],
                $job->payload()
            );

            $logMessage = 'jobqueue.worker completed job {type}';
            $this->channel->basic_ack($message->get('delivery_tag'));
        } catch (InvalidJobException $e) {
            $debugLogContext = $logContext;
            $debugLogContext['exception'] = $e;
            $this->logger->debug(
                'jobqueue.worker started job {type}({payload})',
                $debugLogContext
            );

            $logLevel = LogLevel::WARNING;
            $logMessage = $this->handleFailure($message, $e, $logContext, true);    // discard
        } catch (DiscardException $e) {
            $logLevel = LogLevel::ERROR;
            $logMessage = $this->handleFailure($message, $e, $logContext, true);    // discard
        } catch (ErrorException $e) {
            $logLevel = LogLevel::ERROR;
            $logMessage = $this->handleFailure($message, $e, $logContext);
        } catch (Throwable $e) {
            if (null !== $this->errorHandler) {
                try {
                    $fn = $this->errorHandler;
                    $fn($e);
                } catch (Throwable $e) {
                    $this->isStopping = true;
                    $this->uncaughtThrowable = $e;
                }
            }

            $logLevel = LogLevel::ERROR;
            $logContext['exception'] = $e;
            $logMessage = $this->handleFailure(
                $message,
                new Exception('Internal server error.', $e->getCode()),
                $logContext
            );
        }

        $this->logger->debug(
            'jobqueue.worker finished job {type}({payload})',
            $logContext
        );

        $this->logger->log($logLevel, $logMessage, $logContext);
    }

    /**
     * @param Exception $exception The exception that represents the failure.
     */
    private function handleFailure(
        AMQPMessage $message,
        Throwable $e,
        &$logContext,
        $discard = false
    ) {
        $logContext['code'] = $e->getCode();
        $logContext['reason'] = json_encode($e->getMessage());

        if ($discard) {
            $this->channel->basic_reject($message->get('delivery_tag'), false);

            return 'jobqueue.worker discarding failed job {type} -> {code} {reason}';
        }

        // message failed even after being redelivered so sleep before redelivering
        if ($message->get('redelivered')) {
            usleep(250000);
        }

        $this->channel->basic_reject($message->get('delivery_tag'), true);

        return 'jobqueue.worker requeuing failed job {type} -> {code} {reason}';
    }

    private function bind($type)
    {
        $queue = $this
            ->declarationManager
            ->jobQueue($type);

        $this->consumerTags[$type] = $this
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

    private function unbind($type)
    {
        $this
            ->channel
            ->basic_cancel(
                $this->consumerTags[$type]
            );

        unset($this->consumerTags[$type]);
    }

    private $channel;
    private $errorHandler;
    private $declarationManager;
    private $serialization;
    private $channelDispatcher;
    private $isStopping;
    private $uncaughtThrowable;
    private $handlers;
    private $consumerTags;
}
