<?php
namespace Icecave\Overpass\Serialization;

use Icecave\Overpass\Serialization\Exception\SerializationException;
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
            SerializationException::class,
            'Payload must be an object or an array.'
        );

        $this->serialization->serialize(
            null
        );
    }

    public function testSerializeFailure()
    {
        $this->setExpectedException(
            SerializationException::class,
            'Could not serialize payload.'
        );

        $this->serialization->serialize(
            [fopen(__FILE__, 'r')]
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
            SerializationException::class,
            'Payload must be an object or an array.'
        );

        $this->serialization->unserialize(' null ');
    }

    public function testUnserializeFailure()
    {
        $this->setExpectedException(
            SerializationException::class,
            'Could not unserialize payload.'
        );

        $this->serialization->unserialize('[');
    }
}
