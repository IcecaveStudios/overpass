<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use PHPUnit_Framework_TestCase;

class RpcExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new RpcException('The exception message.');

        $this->assertSame(
            ResponseCode::EXCEPTION(),
            $exception->responseCode()
        );

        $this->assertSame(
            ResponseCode::EXCEPTION,
            $exception->getCode()
        );

        $this->assertSame(
            'The exception message.',
            $exception->getMessage()
        );
    }
}
