<?php
namespace Icecave\Overpass\Rpc\Message;

use Eloquent\Enumeration\Exception\UndefinedMemberException;
use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Serialization\Exception\SerializationException;
use Icecave\Overpass\Serialization\SerializationInterface;

class MessageSerialization implements MessageSerializationInterface
{
    public function __construct(SerializationInterface $serialization)
    {
        $this->serialization = $serialization;
    }

    /**
     * Serialize a request message.
     *
     * @param Request $request
     *
     * @return string
     */
    public function serializeRequest(Request $request)
    {
        $payload = [
            $request->name(),
            $request->arguments(),
        ];

        return $this
            ->serialization
            ->serialize($payload);
    }

    /**
     * Serialize a response message.
     *
     * @param Request $request
     *
     * @return string
     */
    public function serializeResponse(Response $response)
    {
        $payload = [
            $response->code()->value(),
            $response->value(),
        ];

        return $this
            ->serialization
            ->serialize($payload);
    }

    /**
     * Unserialize a request message.
     *
     * @param string $buffer
     *
     * @return Request
     * @throws InvalidMessageException
     */
    public function unserializeRequest($buffer)
    {
        try {
            $payload = $this
                ->serialization
                ->unserialize($buffer);

            if (!is_array($payload)) {
                throw new InvalidMessageException(
                    'Request payload must be a 2-tuple.'
                );
            } elseif (2 !== count($payload)) {
                throw new InvalidMessageException(
                    'Request payload must be a 2-tuple.'
                );
            }

            list($name, $arguments) = $payload;

            if (!is_string($name)) {
                throw new InvalidMessageException(
                    'Request payload procedure name must be a string.'
                );
            } elseif (!is_array($arguments)) {
                throw new InvalidMessageException(
                    'Request payload arguments must be an array.'
                );
            }
        } catch (SerializationException $e) {
            throw new InvalidMessageException(
                'Request payload is invalid.'
            );
        }

        return Request::create($name, $arguments);
    }

    /**
     * Unserialize a response message.
     *
     * @param string $buffer
     *
     * @return Response
     * @throws InvalidMessageException
     */
    public function unserializeResponse($buffer)
    {
        try {
            $payload = $this
                ->serialization
                ->unserialize($buffer);

            if (!is_array($payload)) {
                throw new InvalidMessageException(
                    'Response payload must be a 2-tuple.'
                );
            } elseif (2 !== count($payload)) {
                throw new InvalidMessageException(
                    'Response payload must be a 2-tuple.'
                );
            }

            list($code, $value) = $payload;

            $code = ResponseCode::memberByValue($code);

            if (ResponseCode::SUCCESS() !== $code && !is_string($value)) {
                throw new InvalidMessageException(
                    'Response payload error message must be a string.'
                );
            }
        } catch (SerializationException $e) {
            throw new InvalidMessageException(
                'Response payload is invalid.'
            );
        } catch (UndefinedMemberException $e) {
            throw new InvalidMessageException(
                'Response payload response code is unrecognised.'
            );
        }

        return Response::create($code, $value);
    }

    private $serialization;
}
