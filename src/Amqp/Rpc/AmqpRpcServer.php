<?php
namespace Icecave\Overpass\Amqp;

use Icecave\Overpass\Serialization\SerializationInterface;
use Icecave\Overpass\Rpc\RpcServerInterface;
use AMQPConnection;

class AmqpRpcServer implements RpcServerInterface
{
    /**
     * @param AMQPConnection         $connection
     * @param AmqpDeclarationManager $declarationManager
     * @param SerializationInterface $serialization
     */
    public function __construct(
        AMQPConnection $connection,
        AmqpDeclarationManager $declarationManager,
        SerializationInterface $serialization
    ) {
        $this->connection = $connection;
        $this->declarationManager = $declarationManager;
        $this->serialization = $serialization;
        $this->functions = [];
    }

    /**
     * Register a function with the RPC server.
     *
     * @param string   $name     The name the under which the function is exposed.
     * @param callable $function The function to expose.
     */
    public function register($name, callable $function)
    {

    }

    /**
     * Unregister a function with the RPC server.
     *
     * @param string $name The name the under which the function is exposed.
     */
    public function unregister($name)
    {

    }

    /**
     * Check if the RPC server has a function registered under the given name.
     *
     * @param string $name The name the under which the function is exposed.
     *
     * @param boolean True if there is a function with the given name.
     */
    public function has($name)
    {

    }

    /**
     * Run the RPC server.
     */
    public function run()
    {

    }

    /**
     * Stop the RPC server.
     */
    public function stop()
    {

    }

    private $connection;
    private $declarationManager;
    private $serialization;
}
