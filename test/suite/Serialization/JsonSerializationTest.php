<?php
namespace Icecave\Overpass\Serialization;

use Phake;
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
            '["the-string"]',
            $this->serialization->serialize(['the-string'])
        );
    }

    public function testSerializeWithInvalidPayload()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Payload must be an object or an array.'
        );

        $this->serialization->serialize(
            null
        );
    }

    public function testSerializeFailure()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Could not serialize payload.'
        );

        $this->serialization->serialize(
            [fopen(__FILE__, 'r')]
        );
    }

    public function testSerializeWithSerializableObject()
    {
        $object = Phake::mock(SerializableInterface::class);

        Phake::when($object)
            ->payload()
            ->thenReturn(['the-string']);

        $this->assertSame(
            '["the-string"]',
            $this->serialization->serialize($object)
        );
    }

    public function testUnserialize()
    {
        $this->assertSame(
            ['the-string'],
            $this->serialization->unserialize('["the-string"]')
        );
    }

    public function testUnserializeNull()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Payload must be an object or an array.'
        );

        $this->serialization->unserialize(' null ');
    }

    public function testUnserializeFailure()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Could not unserialize payload.'
        );

        $this->serialization->unserialize('[');
    }
}
