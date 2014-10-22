<?php
namespace Icecave\Overpass\Serialization;

use InvalidArgumentException;
use stdClass;

/**
 * JSON serialization protocol.
 */
class JsonSerialization implements SerializationInterface
{
    /**
     * Serialize the given payload.
     *
     * @param SerializableInterface|stdClass|array $payload
     *
     * @return string
     */
    public function serialize($payload)
    {
        while ($payload instanceof SerializableInterface) {
            $payload = $payload->payload();
        }

        $this->validatePayload($payload);

        $buffer = @json_encode($payload);

        if (is_string($buffer)) {
            return $buffer;
        }

        throw new InvalidArgumentException('Could not serialize payload.');
    }

    /**
     * Unserialize the given buffer.
     *
     * @param string $buffer
     *
     * @return stdClass|array
     */
    public function unserialize($buffer)
    {
        $payload = @json_decode($buffer);

        if (null === $payload && strcasecmp(trim($buffer), 'null') !== 0) {
            throw new InvalidArgumentException('Could not unserialize payload.');
        }

        $this->validatePayload($payload);

        return $payload;
    }

    private function validatePayload($payload)
    {
        if (
            !is_array($payload)
            && !$payload instanceof stdClass
        ) {
            throw new InvalidArgumentException('Payload must be an object or an array.');
        }
    }
}
