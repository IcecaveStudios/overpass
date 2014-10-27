<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;

/**
 * Interface for all RPC excpetions that can occur on the server-side.
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
