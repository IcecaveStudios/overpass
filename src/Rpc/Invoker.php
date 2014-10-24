<?php
namespace Icecave\Overpass\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use ReflectionFunction;
use ReflectionMethod;

class Invoker implements InvokerInterface
{
    /**
     * Invoke a procedure based on a request.
     *
     * @param callable $procedure The procedure to invoke.
     * @param Request  $request   The RPC request.
     *
     * @return Response The RPC response.
     */
    public function invoke(callable $procedure, Request $request)
    {
        $value = null;
        $exception = null;

        try {
            $this->validateArguments(
                $procedure,
                $request->arguments()
            );

            $value = call_user_func_array(
                $procedure,
                $request->arguments()
            );

            return Response::create($value);
        } catch (Exception $e) {
            return Response::createFromException($e);
        }
    }

    public function validateArguments(callable $procedure, array $arguments)
    {
        // TODO

        // // The implementation is a string representing a static method ...
        // if (is_string($implementation) && $pos = strpos($implementation, '::')) {
        //     $this->reflector = new ReflectionMethod(
        //         substr($implementation, 0, $pos),
        //         substr($implementation, $pos + 2)
        //     );

        // // The implementation is an array representing a method ...
        // } elseif (is_array($implementation)) {
        //     list($classOrObject, $method) = $implementation;

        //     $this->reflector = new ReflectionMethod(
        //         $classOrObject,
        //         $method
        //     );

        // // The implementation is a global function, callable object, etc ...
        // } else {
        //     $this->reflector = new ReflectionFunction($implementation);
        // }
    }
}
