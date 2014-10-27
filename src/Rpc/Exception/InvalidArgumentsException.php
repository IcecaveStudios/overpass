<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use InvalidArgumentException;

class InvalidArgumentsException extends InvalidArgumentException implements RpcExceptionInterface
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
        return ResponseCode::INVALID_ARGUMENTS();
    }
}
