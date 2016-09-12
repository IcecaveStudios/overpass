<?php

namespace Icecave\Overpass\JobQueue\Task;

use PHPUnit_Framework_TestCase;

class TaskTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $task = Task::create('task-name', [1, 2, 3]);

        $this->assertSame(
            'task-name',
            $task->jobName()
        );

        $this->assertSame(
            [1, 2, 3],
            $task->payload()
        );
    }

    public function testToString()
    {
        $task = Task::create('task-name', [1, 2, 3]);

        $this->assertSame(
            'task-name([1,2,3])',
            strval($task)
        );
    }
}
