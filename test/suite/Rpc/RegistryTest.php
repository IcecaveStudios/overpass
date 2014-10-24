<?php
namespace Icecave\Overpass\Rpc;

use Icecave\Overpass\Rpc\Exception\UnknownProcedureException;
use PHPUnit_Framework_TestCase;

class RegistryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->procedure = function () {};

        $this->registry = new Registry();
    }

    public function testRegister()
    {
        $this->registry->register('procedure-name', $this->procedure);

        $this->assertTrue(
            $this->registry->has('procedure-name')
        );
    }

    public function testUnregister()
    {
        $this->registry->register('procedure-name', $this->procedure);
        $this->registry->unregister('procedure-name');

        $this->assertFalse(
            $this->registry->has('procedure-name')
        );
    }

    public function testGet()
    {
        $this->registry->register('procedure-name', $this->procedure);

        $this->assertSame(
            $this->procedure,
            $this->registry->get('procedure-name')
        );
    }

    public function testGetFailure()
    {
        $this->setExpectedException(
            UnknownProcedureException::class,
            'Unknown procedure: procedure-name.'
        );

        $this->registry->get('procedure-name');
    }

    public function testProcedures()
    {
        $this->assertSame(
            [],
            $this->registry->procedures()
        );

        $this->registry->register('procedure-1', $this->procedure);

        $this->assertSame(
            ['procedure-1'],
            $this->registry->procedures()
        );

        $this->registry->register('procedure-2', $this->procedure);

        $this->assertSame(
            ['procedure-1', 'procedure-2'],
            $this->registry->procedures()
        );

        $this->registry->unregister('procedure-1');

        $this->assertSame(
            ['procedure-2'],
            $this->registry->procedures()
        );
    }

    public function testIsEmpty()
    {
        $this->assertTrue(
            $this->registry->isEmpty()
        );

        $this->registry->register('procedure-name', $this->procedure);

        $this->assertFalse(
            $this->registry->isEmpty()
        );

        $this->registry->unregister('procedure-name');

        $this->assertTrue(
            $this->registry->isEmpty()
        );
    }
}
