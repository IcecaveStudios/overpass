<?php

namespace Icecave\Overpass\JobQueue;

use Psr\Log\LoggerAwareInterface;

interface WorkerInterface extends LoggerAwareInterface
{
    /**
     * Register a job.
     *
     * @param string   $name    The public name of the job.
     * @param callable $handler The handler to be registered against the job.
     *
     * @throws LogicException if the worker is already running.
     */
    public function register($name, callable $handler);

    /**
     * Register all public methods on an object against jobs of the same name.
     *
     * @param object $object The object with the methods to register.
     * @param string $prefix A string to prefix all the method names with.
     */
    public function registerObject($object, $prefix = '');

    /**
     * Run the worker.
     */
    public function run();

    /**
     * Stop the worker.
     */
    public function stop();
}
