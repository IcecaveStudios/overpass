<?php

namespace Icecave\Overpass\AMQP\Exception;

use RuntimeException;

/**
 * Indicates a situation in which the AMQP connection has been closed.
 */
class ConnectionException extends RuntimeException
{
}
