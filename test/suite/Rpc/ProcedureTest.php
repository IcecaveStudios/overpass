<?php
namespace Icecave\Overpass\Rpc;

use PHPUnit_Framework_TestCase;

class ProcedureTest extends PHPUnit_Framework_TestCase
{
    public function testInvoke()
    {
        $implementation = function ($a, $b) {
            return $a - $b;
        };

        $procedure = new Procedure($implementation);

        $this->assertSame(
            3,
            $procedure->invoke([10, 7])
        );
    }

    public function testInvokeWithInvalidArguments()
    {
        $this->markTestIncomplete();
    }

    public function testValidateArguments()
    {
        $this->markTestIncomplete();
    }
}
