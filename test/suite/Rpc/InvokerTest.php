<?php
namespace Icecave\Overpass\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Exception\InvalidArgumentsException;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use PHPUnit_Framework_TestCase;
use ReflectionFunction;
use stdClass;

class InvokerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->invoker = new Invoker();
    }

    public function testInvokeWithClosure()
    {
        $response = $this->invoker->invoke(
            Request::create('procedure-name', [1, 2, 3]),
            function () {
                return func_get_args();
            }
        );

        $this->assertEquals(
            Response::createFromValue([1, 2, 3]),
            $response
        );
    }

    public function testInvokeWithFunction()
    {
        $response = $this->invoker->invoke(
            Request::create('procedure-name', ['foo']),
            'strlen'
        );

        $this->assertEquals(
            Response::createFromValue(3),
            $response
        );
    }

    public function testInvokeWithMethodAsArray()
    {
        $object = new ReflectionFunction('strlen');

        $response = $this->invoker->invoke(
            Request::create('procedure-name', []),
            [$object, 'getName']
        );

        $this->assertEquals(
            Response::createFromValue('strlen'),
            $response
        );
    }

    public function testInvokeWithMethodAsString()
    {
        $response = $this->invoker->invoke(
            Request::create('procedure-name', ['strlen', true]),
            'ReflectionFunction::export'
        );

        $this->assertEquals(
            Response::createFromValue(
                ReflectionFunction::export('strlen', true)
            ),
            $response
        );
    }

    public function testInvokeWithProcedureThatThrows()
    {
        $exception = new Exception('Error message!');

        $response = $this->invoker->invoke(
            Request::create('procedure-name', [1, 2, 3]),
            function () use ($exception) {
                throw $exception;
            }
        );

        $this->assertEquals(
            Response::createFromException($exception),
            $response
        );
    }

    /**
     * @dataProvider validArgumentTestVectors
     */
    public function testInvokeArgumentChecking(callable $procedure, array $arguments)
    {
        $response = $this->invoker->invoke(
            Request::create('procedure-name', $arguments),
            $procedure
        );

        $this->assertEquals(
            Response::createFromValue(null),
            $response
        );
    }

    /**
     * @dataProvider invalidArgumentTestVectors
     */
    public function testInvokeArgumentCheckingFailure(callable $procedure, array $arguments, $expectedMessage)
    {
        $response = $this->invoker->invoke(
            Request::create('procedure-name', $arguments),
            $procedure
        );

        $this->assertEquals(
            Response::createFromException(
                new InvalidArgumentsException($expectedMessage)
            ),
            $response
        );
    }

    public function validArgumentTestVectors()
    {
        return [
            'arity' => [
                function ($a, $b, $c) { },
                [1, 2, 3],
            ],
            'arity (var args)' => [
                function ($a, $b, $c) { },
                [1, 2, 3, 4, 5],
            ],
            'array type hint' => [
                function (array $a) { },
                [[]],
            ],
            'array type hint (nullable)' => [
                function (array $a = null) { },
                [[]],
            ],
            'array type hint (nullable + with null)' => [
                function (array $a = null) { },
                [null],
            ],
            'callable type hint' => [
                function (callable $a) { },
                [function () {}],
            ],
            'callable type hint (nullable)' => [
                function (callable $a = null) { },
                [function () {}],
            ],
            'callable type hint (nullable + with null)' => [
                function (callable $a = null) { },
                [null],
            ],
            'class type hint' => [
                function (stdClass $a) { },
                [new stdClass],
            ],
            'class type hint (nullable)' => [
                function (stdClass $a = null) { },
                [new stdClass],
            ],
            'class type hint (nullable + with null)' => [
                function (stdClass $a = null) { },
                [null],
            ],
        ];
    }

    public function invalidArgumentTestVectors()
    {
        return [
            'arity' => [
                function ($a, $b, $c) { },
                [],
                'At least 3 arguments are required.',
            ],
            'array type hint' => [
                function (array $a) { },
                [1],
                'Argument "a" must be an array.',
            ],
            'array type hint (nullable)' => [
                function (array $a = null) { },
                [1],
                'Argument "a" must be an array or null.',
            ],
            'callable type hint' => [
                function (callable $a) { },
                [1],
                'Argument "a" must be callable.',
            ],
            'callable type hint (nullable)' => [
                function (callable $a = null) { },
                [1],
                'Argument "a" must be callable or null.',
            ],
            'class type hint' => [
                function (stdClass $a) { },
                [1],
                'Argument "a" must be an instance of "stdClass".',
            ],
            'class type hint (nullable)' => [
                function (stdClass $a = null) { },
                [1],
                'Argument "a" must be an instance of "stdClass" or null.',
            ],
            'class type hint (other class)' => [
                function (stdClass $a) { },
                [$this],
                'Argument "a" must be an instance of "stdClass".',
            ],
            'class type hint (nullable + other class)' => [
                function (stdClass $a = null) { },
                [$this],
                'Argument "a" must be an instance of "stdClass" or null.',
            ],
        ];
    }
}
