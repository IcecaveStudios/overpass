<?php

namespace Icecave\Overpass\JobQueue\Job;

use PHPUnit_Framework_TestCase;

class JobTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $job = Job::create('job-name', [1, 2, 3]);

        $this->assertSame(
            'job-name',
            $job->type()
        );

        $this->assertSame(
            [1, 2, 3],
            $job->payload()
        );
    }

    public function testToString()
    {
        $job = Job::create('job-name', [1, 2, 3]);

        $this->assertSame(
            'job-name([1,2,3])',
            strval($job)
        );
    }
}
