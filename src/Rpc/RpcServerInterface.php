<?php
namespace Icecave\Overpass\Rpc;

interface RpcServerInterface
{
    public function register($name, callable $function);

    public function unregister($name);

    public function has($name);

    public function run();
}
