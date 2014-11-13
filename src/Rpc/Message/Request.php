<?php
namespace Icecave\Overpass\Rpc\Message;

/**
 * Represents an RPC request.
 */
class Request
{
    /**
     * @param string $name      The name of the procedure to call.
     * @param array  $arguments The arguments to pass.
     */
    private function __construct($name, array $arguments)
    {
        $this->name      = $name;
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

    public function __toString()
    {
        return sprintf(
            '%s(%s)',
            $this->name,
            implode(
                ', ',
                array_map(
                    'json_encode',
                    $this->arguments
                )
            )
        );
    }

    private $name;
    private $arguments;
}
