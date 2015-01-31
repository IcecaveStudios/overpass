<?php
namespace Icecave\Overpass\Rpc;

use Exception;
use ICecave\Overpass\Rpc\Exception\ExecutionException;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use Icecave\Overpass\Rpc\Message\ResponseCode;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

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
        $value     = null;
        $exception = null;

        $response = $this->validateArguments(
            $procedure,
            $request->arguments()
        );

        if ($response) {
            return $response;
        }

        try {
            $value = call_user_func_array(
                $procedure,
                $request->arguments()
            );

            return Response::createFromValue($value);
        } catch (ExecutionException $e) {
            return Response::createFromException($e);
        }
    }

    private function validateArguments(callable $procedure, array $arguments)
    {
        $reflector = $this->reflector($procedure);

        $count    = count($arguments);
        $minArity = $reflector->getNumberOfRequiredParameters();
        $maxArity = $reflector->getNumberOfParameters();

        if ($count < $minArity) {
            return Response::create(
                ResponseCode::INVALID_ARGUMENTS(),
                'Not enough arguments - ' . $minArity . ' argument(s) required.'
            );
        }

        $parameters = $reflector->getParameters();

        for ($index = 0; $index < min($count, $maxArity); ++$index) {
            $error = $this->validateArgument(
                $parameters[$index],
                $arguments[$index]
            );

            if ($error) {
                return Response::create(
                    ResponseCode::INVALID_ARGUMENTS(),
                    sprintf(
                        'Argument "%s" %s.',
                        $parameters[$index]->getName(),
                        $error
                    )
                );
            }
        }

        return null;
    }

    private function validateArgument(ReflectionParameter $parameter, $argument)
    {
        if ($parameter->allowsNull()) {
            $suffix = ' or null';

            if (null === $argument) {
                return null;
            }
        } else {
            $suffix = '';
        }

        if ($parameter->isArray()) {
            if (!is_array($argument)) {
                return 'must be an array' . $suffix;
            }
        } elseif ($parameter->isCallable()) {
            if (!is_callable($argument)) {
                return 'must be callable' . $suffix;
            }
        } elseif ($hint = $parameter->getClass()) {
            if (!is_object($argument) || !$hint->isInstance($argument)) {
                return 'must be an instance of "' . $hint->getName() . '"' . $suffix;
            }
        }

        return null;
    }

    private function reflector(callable $procedure)
    {
        // The implementation is a string representing a static method ...
        if (is_string($procedure) && $pos = strpos($procedure, '::')) {
            return new ReflectionMethod(
                substr($procedure, 0, $pos),
                substr($procedure, $pos + 2)
            );

        // The implementation is an array representing a method ...
        } elseif (is_array($procedure)) {
            list($classOrObject, $method) = $procedure;

            return new ReflectionMethod(
                $classOrObject,
                $method
            );

        // The implementation is a global function, callable object, etc ...
        } else {
            return new ReflectionFunction($procedure);
        }
    }
}
