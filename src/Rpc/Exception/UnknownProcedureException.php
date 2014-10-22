<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use LogicException;

class UnknownProcedureException extends LogicException implements RpcExceptionInterface
{
    /**
     * @param string $name The procedure name.
     */
    public function __construct($name)
    {
        parent::__construct(
            'Unknown procedure: ' . $name . '.',
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
        return ResponseCode::UNKNOWN_PROCEDURE();
    }
}
