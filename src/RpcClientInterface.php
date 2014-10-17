<?php
namespace Icecave\Overpass;

interface RpcClientInterface
{
    /**
     * Invoke a remote procedure.
     *
     * @param string $name      The name of the procedure to invoke
     * @param array  $arguments The arguments to pass.
     *
     * @return mixed The return value.
     */
    public function call($name, array $arguments);
}
