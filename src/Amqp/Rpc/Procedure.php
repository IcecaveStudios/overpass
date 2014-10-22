<?php
namespace Icecave\Overpass\Amqp\Rpc;

use ReflectionFunction;
use ReflectionMethod;

/**
 * @internal
 */
class Procedure
{
    public function __construct(callable $implementation)
    {
        $this->implementation = $implementation;

        if (is_string($implementation) && $pos = strpos($implementation, '::')) {
            $this->reflector = new ReflectionMethod(
                substr($implementation, 0, $pos),
                substr($implementation, $pos + 2)
            );
        } elseif (is_array($implementation)) {
            list($classOrObject, $method) = $implementation;

            $this->reflector = new ReflectionMethod(
                $classOrObject,
                $method
            );
        } else {
            $this->reflector = new ReflectionFunction($implementation);
        }
    }

    public function invoke(array $arguments)
    {
        // TODO validate arguments ...
        return call_user_func_array($this->implementation, $arguments);
    }

    private $implementation;
    private $reflector;
}
