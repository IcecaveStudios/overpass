<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use Phake;
use PHPUnit_Framework_TestCase;

class InvalidMessageExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new InvalidMessageException('The exception message.');

        $this->assertSame(
            ResponseCode::INVALID_MESSAGE(),
            $exception->responseCode()
        );

        $this->assertSame(
            ResponseCode::INVALID_MESSAGE,
            $exception->getCode()
        );

        $this->assertSame(
            'The exception message.',
            $exception->getMessage()
        );
    }
}
