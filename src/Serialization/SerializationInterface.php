<?php
namespace Icecave\Overpass\Serialization;

interface SerializationInterface
{
    /**
     * @param mixed $payload
     *
     * @return string
     */
    public function serialize($payload);

    /**
     * @param string $buffer
     *
     * @return mixed
     */
    public function unserialize($buffer);
}
