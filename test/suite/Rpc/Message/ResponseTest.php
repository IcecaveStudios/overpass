<?php
namespace Icecave\Overpass\Rpc\Message;

use Exception;
use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Rpc\Exception\RpcException;
use Icecave\Overpass\Rpc\Exception\UnknownProcedureException;
use LogicException;
use Phake;
use PHPUnit_Framework_TestCase;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $response = Response::create('return-value');

        $this->assertSame(
            ResponseCode::SUCCESS(),
            $response->code()
        );

        $this->assertSame(
            'return-value',
            $response->value()
        );
    }

    public function testCreateFromException()
    {
        $exception = new InvalidMessageException('The exception message.');

        $response = Response::createFromException($exception);

        $this->assertSame(
            $exception->responseCode(),
            $response->code()
        );

        $this->assertSame(
            $exception->getMessage(),
            $response->value()
        );
    }

    public function testCreateFromExceptionWithUnknownException()
    {
        $exception = new LogicException('The exception message.');

        $response = Response::createFromException($exception);

        $this->assertSame(
            ResponseCode::EXCEPTION(),
            $response->code()
        );

        $this->assertSame(
            $exception->getMessage(),
            $response->value()
        );
    }

    public function testCreateFromPayload()
    {
        $response = Response::createFromPayload([ResponseCode::SUCCESS, 'return-value']);

        $this->assertSame(
            ResponseCode::SUCCESS(),
            $response->code()
        );

        $this->assertSame(
            'return-value',
            $response->value()
        );
    }

    public function testCreateFromPayloadWithInvalidExceptionMessage()
    {
        $this->setExpectedException(
            InvalidMessageException::class,
            'Error message must be a string.'
        );

        Response::createFromPayload([ResponseCode::EXCEPTION, null]);
    }

    public function testExtract()
    {
        $response = Response::create('return-value');

        $this->assertSame(
            'return-value',
            $response->extract()
        );
    }

    /**
     * @dataProvider extractTestVectors
     */
    public function testExtractWithException(Exception $exception, $expectedClass = null)
    {
        $response = Response::createFromException($exception);

        $this->setExpectedException(
            $expectedClass ?: get_class($exception),
            $exception->getMessage()
        );

        $response->extract();
    }

    public function testPayload()
    {
        $this->assertSame(
            [ResponseCode::SUCCESS, 'return-value'],
            Response::create('return-value')->payload()
        );
    }

    public function extractTestVectors()
    {
        return [
            [new LogicException('The exception message.'), RpcException::class],
            [new InvalidMessageException('The exception message.')],
            [new UnknownProcedureException('procedure-name')],
        ];
    }
}
