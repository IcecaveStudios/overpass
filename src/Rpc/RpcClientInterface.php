<?php
namespace Icecave\Overpass\Rpc;

use Psr\Log\LoggerAwareInterface;

interface RpcClientInterface extends LoggerAwareInterface
{
    /**
     * Invoke a remote procedure.
     *
     * @param string $name         The name of the procedure to invoke
     * @param mixed  $argument,... The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function invoke($name);

    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function invokeArray($name, array $arguments);

    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function __call($name, array $arguments);

    /**
     * Get the RPC response timeout.
     *
     * @return integer|float The RPC response timeout in seconds.
     */
    public function timeout();

    /**
     * Set the RPC response timeout.
     *
     * @param integer|float The RPC response timeout in seconds.
     */
    public function setTimeout($timeout);
}
