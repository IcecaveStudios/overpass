<?php
namespace Icecave\Overpass\Rpc;

use InvalidArgumentException;
use ReflectionFunction;
use ReflectionMethod;

class Procedure implements ProcedureInterface
{
    /**
     * @param callable $implementation
     */
    public function __construct(callable $implementation)
    {
        $this->implementation = $implementation;

        // The implementation is a string representing a static method ...
        if (is_string($implementation) && $pos = strpos($implementation, '::')) {
            $this->reflector = new ReflectionMethod(
                substr($implementation, 0, $pos),
                substr($implementation, $pos + 2)
            );

        // The implementation is an array representing a method ...
        } elseif (is_array($implementation)) {
            list($classOrObject, $method) = $implementation;

            $this->reflector = new ReflectionMethod(
                $classOrObject,
                $method
            );

        // The implementation is a global function, callable object, etc ...
        } else {
            $this->reflector = new ReflectionFunction($implementation);
        }
    }

    /**
     * Invoke the procedure.
     *
     * @param array The arguments to pass to the procedure.
     *
     * @return mixed                    The procedure result.
     * @throws InvalidArgumentException if the arguments are invalid.
     */
    public function invoke(array $arguments)
    {
        if (!$this->validateArguments($arguments)) {
            throw new InvalidArgumentException('Invalid arguments.');
        }

        return call_user_func_array(
            $this->implementation,
            $arguments
        );
    }

    /**
     * Check that the given arguments may be used to invoke this procedure.
     *
     * @return boolean True if the given arguments are valid; otherwise, false.
     */
    public function validateArguments(array $arguments)
    {
        return true; // TODO
    }

    private $implementation;
    private $reflector;
}
