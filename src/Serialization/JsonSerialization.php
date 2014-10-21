<?php
namespace Icecave\Overpass\Serialization;

use InvalidArgumentException;

class JsonSerialization implements SerializationInterface
{
    /**
     * @param mixed $payload
     *
     * @return string
     */
    public function serialize($payload)
    {
        $buffer = @json_encode($payload);

        if (is_string($buffer)) {
            return $buffer;
        }

        throw new InvalidArgumentException('Could not serialize payload.');
    }

    /**
     * @param string $buffer
     *
     * @return mixed
     */
    public function unserialize($buffer)
    {
        $payload = @json_decode($buffer);

        if ($payload !== null) {
            return $payload;
        } elseif (strcasecmp(trim($buffer), 'null') === 0) {
            return false;
        }

        throw new InvalidArgumentException('Could not unserialize payload.');
    }
}
