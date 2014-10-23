<?php
namespace Icecave\Overpass\Rpc;

use InvalidArgumentException;

interface ProcedureInterface
{
    /**
     * Invoke the procedure.
     *
     * @param array The arguments to pass to the procedure.
     *
     * @return mixed                    The procedure result.
     * @throws InvalidArgumentException if the arguments are invalid.
     */
    public function invoke(array $arguments);

    /**
     * Check that the given arguments may be used to invoke this procedure.
     *
     * @return boolean True if the given arguments are valid; otherwise, false.
     */
    public function validateArguments(array $arguments);
}
