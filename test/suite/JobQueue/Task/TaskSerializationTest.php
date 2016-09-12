<?php

namespace Icecave\Overpass\JobQueue\Task;

use Icecave\Overpass\JobQueue\Exception\InvalidTaskException;
use Icecave\Overpass\Serialization\JsonSerialization;
use PHPUnit_Framework_TestCase;

class TaskSerializationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->task = Task::create('task-name', [1, 2, 3]);
        $this->buffer = '["task-name",[1,2,3]]';

        $this->serialization = new TaskSerialization(
            new JsonSerialization()
        );
    }

    public function testSerializeTask()
    {
        $buffer = $this
            ->serialization
            ->serializeTask($this->task);

        $this->assertEquals(
            $this->buffer,
            $buffer
        );
    }

    public function testUnserializeTask()
    {
        $request = $this
            ->serialization
            ->unserializeTask($this->buffer);

        $this->assertEquals(
            $this->task,
            $request
        );
    }

    /**
     * @dataProvider invalidTaskTestVectors
     */
    public function testUnserializeTaskFailure($message, $buffer)
    {
        $this->setExpectedException(
            InvalidTaskException::class,
            $message
        );

        $this
            ->serialization
            ->unserializeTask($buffer);
    }

    public function invalidTaskTestVectors()
    {
        return [
            ['Task request is invalid.',                      '['],
            ['Task request must be a 2-tuple.',               '{}'],
            ['Task request must be a 2-tuple.',               '[1]'],
            ['Task request must be a 2-tuple.',               '[1, 2, 3]'],
            ['Job name must be a string.', '[null, []]'],
        ];
    }
}
