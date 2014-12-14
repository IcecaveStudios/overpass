<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;

/**
 * Interface for all RPC exceptions that can occur on the server-side.
 */
interface RemoteExceptionInterface
{
    /**
     * Get the exception message.
     *
     * @return string
     */
    public function getMessage();

    /**
     * Get the response code.
     *
     * @return ResponseCode
     */
    public function responseCode();
}
