<?php
namespace Icecave\Overpass\Rpc\Message;

use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Serialization\JsonSerialization;
use PHPUnit_Framework_TestCase;

class MessageSerializationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->request  = Request::create('procedure-name', [1, 2, 3]);
        $this->response = Response::createFromValue('<return-value>');

        $this->requestBuffer  = '["procedure-name",[1,2,3]]';
        $this->responseBuffer = '[0,"<return-value>"]';

        $this->serialization = new MessageSerialization(
            new JsonSerialization()
        );
    }

    public function testSerializeRequest()
    {
        $buffer = $this
            ->serialization
            ->serializeRequest($this->request);

        $this->assertEquals(
            $this->requestBuffer,
            $buffer
        );
    }

    public function testSerializeResponse()
    {
        $buffer = $this
            ->serialization
            ->serializeResponse($this->response);

        $this->assertEquals(
            $this->responseBuffer,
            $buffer
        );
    }

    public function testUnserializeRequest()
    {
        $request = $this
            ->serialization
            ->unserializeRequest($this->requestBuffer);

        $this->assertEquals(
            $this->request,
            $request
        );
    }

    /**
     * @dataProvider invalidRequestTestVectors
     */
    public function testUnserializeRequestFailure($message, $buffer)
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            $message
        );

        $this
            ->serialization
            ->unserializeRequest($buffer);
    }

    public function invalidRequestTestVectors()
    {
        return [
            ['Request payload is invalid.',                      '['],
            ['Request payload must be a 2-tuple.',               '{}'],
            ['Request payload must be a 2-tuple.',               '[1]'],
            ['Request payload must be a 2-tuple.',               '[1, 2, 3]'],
            ['Request payload procedure name must be a string.', '[null, []]'],
            ['Request payload arguments must be an array.',      '["", null]'],
        ];
    }

    public function testUnserializeResponse()
    {
        $response = $this
            ->serialization
            ->unserializeResponse($this->responseBuffer);

        $this->assertEquals(
            $this->response,
            $response
        );
    }

    /**
     * @dataProvider invalidResponseTestVectors
     */
    public function testUnserializeResponseFailure($message, $buffer)
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            $message
        );

        $this
            ->serialization
            ->unserializeResponse($buffer);
    }

    public function invalidResponseTestVectors()
    {
        return [
            ['Response payload is invalid.',                     '['],
            ['Response payload must be a 2-tuple.',              '{}'],
            ['Response payload must be a 2-tuple.',              '[1]'],
            ['Response payload must be a 2-tuple.',              '[1, 2, 3]'],
            ['Response payload response code is unrecognised.',  '[-1, []]'],
            ['Response payload error message must be a string.', '[' . ResponseCode::EXCEPTION . ', null]'],
        ];
    }
}
