<?php
namespace Icecave\Overpass\Amqp\Rpc;

use Icecave\Overpass\Amqp\DeclarationManagerInterface;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * @access private
 */
class DeclarationManager implements DeclarationManagerInterface
{
    public function __construct(AMQPChannel $channel)
    {
        $this->channel       = $channel;
        $this->requestQueues = [];
    }

    /**
     * @return string
     */
    public function exchange()
    {
        if ($this->exchange) {
            return $this->exchange;
        }

        $name = 'overpass/rpc';

        $this
            ->channel
            ->exchange_declare(
                $name,
                'direct',
                false, // passive,
                false, // durable,
                false  // auto delete
            );

        $this->exchange = $name;

        return $this->exchange;
    }

    /**
     * @param string $procedureName
     *
     * @return string
     */
    public function requestQueue($procedureName)
    {
        if (isset($this->requestQueues[$procedureName])) {
            return $this->requestQueues[$procedureName];
        }

        list($queueName) = $this
            ->channel
            ->queue_declare(
                'overpass/rpc/' . $procedureName,
                false, // passive
                false, // durable
                false, // exclusive
                false  // auto delete
            );

        $this
            ->channel
            ->queue_bind(
                $queueName,
                $this->exchange(),
                $procedureName
            );

        $this->requestQueues[$procedureName] = $queueName;

        return $queueName;
    }

    /**
     * @return string
     */
    public function responseQueue()
    {
        if ($this->responseQueue) {
            return $this->responseQueue;
        }

        list($this->responseQueue) = $this
            ->channel
            ->queue_declare(
                '',
                false, // passive
                false, // durable
                true,  // exclusive
                true   // auto delete
            );

        return $this->responseQueue;
    }

    /**
     * Fake a heartbeat.
     *
     * @return string
     */
    public function heartbeat()
    {
        $this->exchange = null;

        return $this->exchange();
    }

    private $exchange;
    private $requestQueues;
    private $responseQueue;
}
