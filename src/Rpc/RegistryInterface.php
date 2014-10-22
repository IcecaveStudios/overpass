<?php
namespace Icecave\Overpass\Rpc;

interface RegistryInterface
{
    /**
     * Check if the given procedure is registered.
     *
     * @param string $name The public name of the procedure.
     *
     * @param boolean True if there is a procedure with the given name; otherwise, false.
     */
    public function has($name);

    /**
     * Get the implementation for the given procedure.
     *
     * @param string $name The public name of the procedure.
     *
     * @return ProcedureInterface The procedure with the given name.
     * @throws UnknownProcedureException if the given procedure is not registered.
     */
    public function get($name);

    /**
     * Get a list of all registered procedure names.
     *
     * @return array<string> An array containing the public names of all registered procedures.
     */
    public function procedures();

    /**
     * Indicates whether or not the registry is empty.
     *
     * @return boolean True if the registry is empty; otherwise, false.
     */
    public function isEmpty();

    /**
     * Register a procedure.
     *
     * @param string                      $name      The public name of the procedure.
     * @param ProcedureInterface|callable $procedure The procedure to register.
     */
    public function register($name, $procedure);

    /**
     * Unregister a procedure.
     *
     * @param string $name The public name of the procedure.
     */
    public function unregister($name);
}
