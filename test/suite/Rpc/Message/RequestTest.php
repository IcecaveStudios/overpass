<?php
namespace Icecave\Overpass\Rpc\Message;

use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $request = Request::create('procedure-name', [1, 2, 3]);

        $this->assertSame(
            'procedure-name',
            $request->name()
        );

        $this->assertSame(
            [1, 2, 3],
            $request->arguments()
        );
    }

    public function testCreateFromPayload()
    {
        $request = Request::createFromPayload(['procedure-name', [1, 2, 3]]);

        $this->assertSame(
            'procedure-name',
            $request->name()
        );

        $this->assertSame(
            [1, 2, 3],
            $request->arguments()
        );
    }

    public function testCreateFromPayloadWithInvalidProcedureName()
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            'Procedure name must be a string.'
        );

        Request::createFromPayload([null, []]);
    }

    public function testCreateFromPayloadWithInvalidArguments()
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            'Procedure arguments must be an array.'
        );

        Request::createFromPayload(['procedure-name', null]);
    }

    public function testPayload()
    {
        $this->assertSame(
            ['procedure-name', [1, 2, 3]],
            Request::create('procedure-name', [1, 2, 3])->payload()
        );
    }
}
