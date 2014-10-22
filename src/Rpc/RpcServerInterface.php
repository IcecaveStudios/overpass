<?php
namespace Icecave\Overpass\Rpc;

interface RpcServerInterface
{
    /**
     * Register a function with the RPC server.
     *
     * @param string   $name     The name the under which the function is exposed.
     * @param callable $function The function to expose.
     */
    public function register($name, callable $function);

    /**
     * Unregister a function with the RPC server.
     *
     * @param string $name The name the under which the function is exposed.
     */
    public function unregister($name);

    /**
     * Check if the RPC server has a function registered under the given name.
     *
     * @param string $name The name the under which the function is exposed.
     *
     * @param boolean True if there is a function with the given name.
     */
    public function has($name);

    /**
     * Run the RPC server.
     */
    public function run();

    /**
     * Stop the RPC server.
     */
    public function stop();
}
