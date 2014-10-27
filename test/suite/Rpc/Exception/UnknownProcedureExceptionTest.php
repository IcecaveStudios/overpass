<?php
namespace Icecave\Overpass\Rpc\Exception;

use Icecave\Overpass\Rpc\Message\ResponseCode;
use PHPUnit_Framework_TestCase;

class UnknownProcedureExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $exception = new UnknownProcedureException('procedure-name');

        $this->assertSame(
            ResponseCode::UNKNOWN_PROCEDURE(),
            $exception->responseCode()
        );

        $this->assertSame(
            ResponseCode::UNKNOWN_PROCEDURE,
            $exception->getCode()
        );

        $this->assertSame(
            'Unknown procedure: procedure-name.',
            $exception->getMessage()
        );
    }
}
