<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use LogicException;

class InvalidMessageException extends LogicException implements RpcExceptionInterface
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
        return ResponseCode::INVALID_MESSAGE();
    }
}
