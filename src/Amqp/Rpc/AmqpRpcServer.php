<?php
namespace Icecave\Overpass\Amqp;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Icecave\Isolator\IsolatorTrait;

class AmqpRpcServer implements RpcServerInterface
{
    use IsolatorTrait;

    public function __construct(AMQPConnection $connection)
    {
        $this->connection = $connection;
        $this->procedures = [];
    }

    public function register($name, callable $function)
    {
        $this->procedures[$name] = $function;
    }

    public function unregister($name)
    {
        unset($this->procedures[$name]);
    }

    public function has($name)
    {
        return isset($this->procedures[$name]);
    }

    public function run()
    {

    }

    /**
     * @return AMQPChannel
     */
    private function channel()
    {
        if (!$this->channel) {
            $this->channel = $this->isolator()->new(
                AMQPChannel::class,
                $this->connection
            );

            $this->channel->setPrefetchCount(1);
        }

        return $this->channel;
    }

    /**
     * @return AMQPExchange
     */
    private function exchange()
    {
        if (!$this->exchange) {
            $this->exchange = $this->isolator()->new(
                AMQPExchange::class,
                $this->channel()
            );

            $this->exchange->setName('overpass.pubsub');
            $this->exchange->setType(AMQP_EX_TYPE_TOPIC);
            $this->exchange->declareExchange();
        }

        return $this->exchange;
    }

    /**
     * @return AMQPQueue
     */
    private function queue()
    {
        if (!$this->queue) {
            $this->queue = $this->isolator()->new(
                AMQPQueue::class,
                $this->channel()
            );

            $this->queue->setFlags(AMQP_EXCLUSIVE);
            $this->queue->declareQueue();
        }

        return $this->queue;
    }

    private $connection;
    private $exchange;
    private $queue;
    private $procedures;
}
