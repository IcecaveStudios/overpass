<?php
namespace Icecave\Overpass\Serialization;

use stdClass;

/**
 * Defines a serialization protocol.
 */
interface SerializationInterface
{
    /**
     * Serialize the given payload.
     *
     * @param SerializableInterface|stdClass|array $payload
     *
     * @return string
     */
    public function serialize($payload);

    /**
     * Unserialize the given buffer.
     *
     * @param string $buffer
     *
     * @return stdClass|array
     */
    public function unserialize($buffer);
}
