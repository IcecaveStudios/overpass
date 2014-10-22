<?php
namespace Icecave\Overpass\Rpc\Exception;

use Exception;
use Icecave\Overpass\Rpc\Message\ResponseCode;

/**
 * Represents an arbitrary exception that occurred while invoking a procedure.
 */
class RpcException extends Exception implements RpcExceptionInterface
{
    /**
     * @param string $message The exception message.
     */
    public function __construct($message)
    {
        parent::__construct(
            $message,
            $this->responseCode()->value()
        );
    }

    /**
     * Get the response code.
     *
     * @return ResponseCode
     */
    public function responseCode()
    {
        return ResponseCode::EXCEPTION();
    }
}
