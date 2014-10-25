<?php
namespace Icecave\Overpass\Rpc;

use Exception;
use Icecave\Overpass\Rpc\Message\Request;
use Icecave\Overpass\Rpc\Message\Response;
use PHPUnit_Framework_TestCase;

class InvokerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->invoker = new Invoker();
    }

    public function testInvoke()
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

    public function testInvokeWithException()
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
}
