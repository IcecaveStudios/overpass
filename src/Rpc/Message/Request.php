<?php
namespace Icecave\Overpass\Rpc\Message;

use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Serialization\SerializableInterface;

/**
 * Represents an RPC request.
 */
class Request implements SerializableInterface
{
    /**
     * @param string $name      The name of the procedure to call.
     * @param array  $arguments The arguments to pass.
     */
    private function __construct($name, array $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * Create a request.
     *
     * @param string $name      The name of the procedure to call.
     * @param array  $arguments The arguments to pass.
     *
     * @return Request
     */
    public static function create($name, array $arguments)
    {
        return new static($name, $arguments);
    }

    /**
     * Create a request from payload data.
     *
     * @param array $payload
     *
     * @return Request
     */
    public static function createFromPayload($payload)
    {
        list($name, $arguments) = $payload;

        if (!is_string($name)) {
            throw new InvalidMessageException('Procedure name must be a string.');
        }

        if (!is_array($arguments)) {
            throw new InvalidMessageException('Procedure arguments must be an array.');
        }

        return self::create($name, $arguments);
    }

    /**
     * Get the name of the procedure to call.
     *
     * @return string The name of the procedure to call.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the arguments to pass.
     *
     * @return array The arguments to pass.
     */
    public function arguments()
    {
        return $this->arguments;
    }

    /**
     * Get the object's serializable payload.
     *
     * @return array
     */
    public function payload()
    {
        return [$this->name, $this->arguments];
    }

    private $name;
    private $arguments;
}
