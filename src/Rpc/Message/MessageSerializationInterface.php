<?php
namespace Icecave\Overpass\Rpc\Message;

use Icecave\Overpass\Rpc\Exception\InvalidMessageException;

interface MessageSerializationInterface
{
    /**
     * Serialize a request message.
     *
     * @param Request $request
     *
     * @return string
     */
    public function serializeRequest(Request $request);

    /**
     * Serialize a response message.
     *
     * @param Request $request
     *
     * @return string
     */
    public function serializeResponse(Response $response);

    /**
     * Unserialize a request message.
     *
     * @param string $buffer
     *
     * @return Request
     * @throws InvalidMessageException
     */
    public function unserializeRequest($buffer);

    /**
     * Unserialize a response message.
     *
     * @param string $buffer
     *
     * @return Response
     * @throws InvalidMessageException
     */
    public function unserializeResponse($buffer);
}
