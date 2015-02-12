<?php
namespace Icecave\Overpass\Amqp;

use ErrorException;
use Icecave\Isolator\IsolatorTrait;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;

/**
 * @access private
 *
 * This class provides a wait() implementation that can handle interruption
 * by signals.
 */
class ChannelDispatcher
{
    use IsolatorTrait;

    public function __construct($timeout = 30)
    {
        $this->timeout = $timeout;
    }

    public function wait(AMQPChannel $channel)
    {
        try {
            // Any non-zero timeout causes the AMQP library to use
            // stream_select() to wait for activity.
            $channel->wait(
                null,
                false,
                $this->timeout
            );
        } catch (ErrorException $e) {
            if (false === strpos($e->getMessage(), 'Interrupted system call')) {
                throw $e;
            }
        } catch (AMQPTimeoutException $e) {
            // ignore ...
        }

        $this->isolator()->pcntl_signal_dispatch();
    }

    private $timeout;
}
