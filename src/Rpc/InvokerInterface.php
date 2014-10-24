<?php
namespace Icecave\Overpass\Rpc;

use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;

interface InvokerInterface
{
    /**
     * Invoke a procedure based on a request.
     *
     * @param callable $procedure The procedure to invoke.
     * @param Request  $request   The RPC request.
     *
     * @return Response The RPC response.
     */
    public function invoke(callable $procedure, Request $request);
}
