<?php
namespace Icecave\Overpass\Rpc\Exception;

use Exception;
use RuntimeException;

/**
 * Indicates that a timeout has occurred.
 */
class TimeoutException extends RuntimeException
{
    public function __construct($timeout, Exception $previous = null)
    {
        parent::__construct(
            'RPC call timed out after ' . $timeout . ' seconds.',
            0,
            $previous
        );
    }
}
