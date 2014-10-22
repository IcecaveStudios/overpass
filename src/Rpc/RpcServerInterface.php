<?php
namespace Icecave\Overpass\Rpc;

interface RpcServerInterface
{
    /**
     * Get the registry used by this server to resolve procedure names.
     *
     * @return RegistryInterface The procedure registry.
     */
    public function registry();

    /**
     * Run the RPC server.
     */
    public function run();

    /**
     * Stop the RPC server.
     */
    public function stop();
}
