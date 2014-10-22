<?php
namespace Icecave\Overpass\Serialization;

use stdClass;

/**
 * An object that is capable of producing it's own serialization payload.
 */
interface SerializableInterface
{
    /**
     * Get the object's serializable payload.
     *
     * @return SerializableInterface|stdClass|array
     */
    public function payload();
}
