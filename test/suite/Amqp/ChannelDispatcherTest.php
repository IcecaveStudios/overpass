<?php

namespace Icecave\Overpass\Amqp;

use ErrorException;
use Icecave\Isolator\Isolator;
use Phake;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PHPUnit_Framework_TestCase;

class ChannelDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->channel  = Phake::mock(AMQPChannel::class);
        $this->isolator = Phake::mock(Isolator::class);

        $this->dispatcher = new ChannelDispatcher;
        $this->dispatcher->setIsolator($this->isolator);
    }

    public function testWait()
    {
        $this->dispatcher->wait($this->channel);

        Phake::verify($this->channel)->wait(null, false, 30);
        Phake::verify($this->isolator)->pcntl_signal_dispatch();
    }

    public function testWaitWithSignalInterrupt()
    {
        $exception = new ErrorException(
            'stream_select(): unable to select [4]: Interrupted system call (max_fd=5)'
        );

        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenThrow($exception);

        $this->dispatcher->wait($this->channel);

        Phake::verify($this->channel)->wait(null, false, 30);
        Phake::verify($this->isolator)->pcntl_signal_dispatch();
    }

    public function testWaitWithGenericException()
    {
        $exception = new ErrorException('The exception!');

        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenThrow($exception);

        $this->setExpectedException(
            ErrorException::class,
            'The exception!'
        );

        $this->dispatcher->wait($this->channel);
    }

    public function testWaitWithTimeoutException()
    {
        $exception = new AMQPTimeoutException;

        Phake::when($this->channel)
            ->wait(Phake::anyParameters())
            ->thenThrow($exception);

        $this->dispatcher->wait($this->channel);

        Phake::verify($this->channel)->wait(null, false, 30);
        Phake::verify($this->isolator)->pcntl_signal_dispatch();
    }
}
