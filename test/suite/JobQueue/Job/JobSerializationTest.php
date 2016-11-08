<?php

namespace Icecave\Overpass\JobQueue\Job;

use Icecave\Overpass\JobQueue\Exception\InvalidJobException;
use Icecave\Overpass\Serialization\JsonSerialization;
use PHPUnit_Framework_TestCase;

class JobSerializationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->job = Job::create('job-name', [1, 2, 3]);
        $this->buffer = '["job-name",[1,2,3]]';

        $this->serialization = new JobSerialization(
            new JsonSerialization()
        );
    }

    public function testSerializeJob()
    {
        $buffer = $this
            ->serialization
            ->serializeJob($this->job);

        $this->assertEquals(
            $this->buffer,
            $buffer
        );
    }

    public function testUnserializeJob()
    {
        $request = $this
            ->serialization
            ->unserializeJob($this->buffer);

        $this->assertEquals(
            $this->job,
            $request
        );
    }

    /**
     * @dataProvider invalidJobTestVectors
     */
    public function testUnserializeJobFailure($message, $buffer)
    {
        $this->setExpectedException(
            InvalidJobException::class,
            $message
        );

        $this
            ->serialization
            ->unserializeJob($buffer);
    }

    public function invalidJobTestVectors()
    {
        return [
            ['Job request is invalid.',                      '['],
            ['Job request must be a 2-tuple.',               '{}'],
            ['Job request must be a 2-tuple.',               '[1]'],
            ['Job request must be a 2-tuple.',               '[1, 2, 3]'],
            ['Job type must be a string.', '[null, []]'],
        ];
    }
}
