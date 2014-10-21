<?php
namespace Icecave\Overpass\Serialization;

use PHPUnit_Framework_TestCase;

class JsonSerializationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serialization = new JsonSerialization();
    }

    public function testSerialize()
    {
        $this->assertSame(
            '"bar"',
            $this->serialization->serialize('bar')
        );
    }

    public function testSerializeFailure()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->serialization->serialize(fopen(__FILE__, 'r'));
    }

    public function testUnserialize()
    {
        $this->assertSame(
            'bar',
            $this->serialization->unserialize('"bar"')
        );
    }

    public function testUnserializeNull()
    {
        $this->assertNull(
            $this->serialization->unserialize(' null ')
        );
    }

    public function testUnserializeFailure()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->serialization->unserialize('[');
    }
}
