<?php
namespace Icecave\Overpass\Rpc\Message;

use Exception;
use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Rpc\Exception\RpcException;
use Icecave\Overpass\Rpc\Exception\RpcExceptionInterface;
use Icecave\Overpass\Rpc\Exception\UnknownProcedureException;

/**
 * Represents an RPC response.
 */
class Response
{
    /**
     * @param ResponseCode The response code.
     * @param mixed The return value or exception message.
     */
    private function __construct(ResponseCode $code, $value)
    {
        $this->code = $code;
        $this->value = $value;
    }

    /**
     * Create a response.
     *
     * @param ResponseCode The response code.
     * @param mixed The return value or exception message.
     *
     * @return Response
     */
    public static function create(ResponseCode $code, $value)
    {
        return new static(
            $code,
            $value
        );
    }

    /**
     * Create a success response.
     *
     * @param mixed $value The return value.
     *
     * @return Response
     */
    public static function createFromValue($value)
    {
        return new static(
            ResponseCode::SUCCESS(),
            $value
        );
    }

    /**
     * Create a failure response.
     *
     * @param Exception $exception The exception that occurred.
     *
     * @return Response
     */
    public static function createFromException(Exception $exception)
    {
        if (!$exception instanceof RpcExceptionInterface) {
            $exception = new RpcException(
                $exception->getMessage()
            );
        }

        return new static(
            $exception->responseCode(),
            $exception->getMessage()
        );
    }

    /**
     * Get the response code.
     *
     * @return ResponseCode
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Get the response value.
     *
     * For a successful response this is the return value of the procedure,
     * otherwise this is the exception message.
     *
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Extract the return value or exception.
     *
     * @return mixed                 The return value (if the response was successful).
     * @throws RpcExceptionInterface if the response was not successful.
     */
    public function extract()
    {
        switch ($this->code) {
            case ResponseCode::SUCCESS():
                return $this->value;
            case ResponseCode::INVALID_MESSAGE():
                throw new InvalidMessageException($this->value);
            case ResponseCode::UNKNOWN_PROCEDURE():
                throw new UnknownProcedureException($this->value);
        }

        // ResponseCode::EXCEPTION()
        throw new RpcException($this->value);
    }

    public function __toString()
    {
        if (ResponseCode::SUCCESS() === $this->code) {
            return json_encode($this->value);
        }

        return sprintf(
            '%s (%s)',
            $this->code,
            $this->value
        );
    }

    private $code;
    private $value;
}
