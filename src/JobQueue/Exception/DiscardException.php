<?php

namespace Icecave\Overpass\JobQueue\Exception;

use RuntimeException;

/**
 * Indicates a situation in which a job failed to be processed and MUST be
 * discarded instead being attempted again.
 */
class DiscardException extends RuntimeException
{
}
