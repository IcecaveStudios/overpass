<?php
namespace Icecave\Overpass\Rpc\Message;

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

    public function testToString()
    {
        $request = Request::create('procedure-name', [1, 2, 3]);

        $this->assertSame(
            'procedure-name(1, 2, 3)',
            strval($request)
        );
    }
}
