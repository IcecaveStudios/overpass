<?php
namespace Icecave\Overpass\Rpc;

interface RpcServerInterface
{
    /**
     * Register a procedure with the RPC server.
     *
     * @param string   $name      The name the under which the procedure is exposed.
     * @param callable $procedure The procedure to expose.
     */
    public function register($name, callable $procedure);

    /**
     * Unregister a procedure with the RPC server.
     *
     * @param string $name The name the under which the procedure is exposed.
     */
    public function unregister($name);

    /**
     * Check if the RPC server has a procedure registered under the given name.
     *
     * @param string $name The name the under which the procedure is exposed.
     *
     * @param boolean True if there is a procedure with the given name.
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
