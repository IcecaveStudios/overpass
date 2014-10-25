<?php
namespace Icecave\Overpass\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Exception\InvalidArgumentsException;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use ReflectionFunction;
use ReflectionMethod;

class Invoker implements InvokerInterface
{
    /**
     * Invoke a procedure based on a request.
     *
     * @param Request  $request   The RPC request.
     * @param callable $procedure The procedure to invoke.
     *
     * @return Response The RPC response.
     */
    public function invoke(Request $request, callable $procedure)
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

            return Response::createFromValue($value);
        } catch (Exception $e) {
            return Response::createFromException($e);
        }
    }

    private function validateArguments(callable $procedure, array $arguments)
    {
        $reflector = $this->reflector($procedure);

        $arity = $reflector->getNumberOfRequiredParameters();

        if (count($arguments) < $arity) {
            throw new InvalidArgumentsException(
                'At least ' . $arity . ' arguments are required.'
            );
        }

        $parameters = $reflector->getParameters();

        $index = 0;

        foreach ($arguments as $argument) {
            $parameter = $parameters[$index++];

            $error = $this->validateArgument(
                $parameter,
                $argument
            );

            if ($error) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Argument %s: %s',
                        $parameter->getName(),
                        $error
                    )
                );
            }
        }
    }

    private function validateArgument(ReflectionParameter $parameter, $argument)
    {
        if (null === $argument && $parameter->allowNull()) {
            return null;
        } elseif ($parameter->isArray()) {
            if (!is_array($argument)) {
                return 'Must be an array.';
            }
        } elseif ($parameter->isCallable())

        if (null === $argument) {
            if (!$parameter->allowsNull()) {
                return 'Must not be null.'
                throw new InvalidArgumentException(
                    'Argument ' . $parameter
                );
            }
        }
        if ($parameter->isArray()) {

        }
            if ($hint = $parameter->getClass()) {
                $hint->isInstance($argument)

                throw new InvalidArgumentsException(
                    'Argument ' . $parameter->getName() . ' must be an instance of ' . $hint->getName() . '.'
                );
            }

            if ($parameter->isArray())

    }

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

    private function reflector(callable $procedure)
    {
        // The implementation is a string representing a static method ...
        if (is_string($implementation) && $pos = strpos($implementation, '::')) {
            return new ReflectionMethod(
                substr($implementation, 0, $pos),
                substr($implementation, $pos + 2)
            );

        // The implementation is an array representing a method ...
        } elseif (is_array($implementation)) {
            list($classOrObject, $method) = $implementation;

            return new ReflectionMethod(
                $classOrObject,
                $method
            );

        // The implementation is a global function, callable object, etc ...
        } else {
            return new ReflectionFunction($implementation);
        }
    }
}
