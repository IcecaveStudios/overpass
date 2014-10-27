<?php
namespace Icecave\Overpass\Rpc\Message;

use Exception;
use Icecave\Overpass\Rpc\Exception\ExecutionException;
use Icecave\Overpass\Rpc\Exception\InvalidMessageException;
use Icecave\Overpass\Rpc\Exception\UnknownProcedureException;
use LogicException;
use PHPUnit_Framework_TestCase;

class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $code  = ResponseCode::SUCCESS();
        $value = '<value>';

        $response = Response::create($code, $value);

        $this->assertSame(
            $code,
            $response->code()
        );

        $this->assertSame(
            '<value>',
            $response->value()
        );
    }

    public function testCreateFromValue()
    {
        $response = Response::createFromValue('<return-value>');

        $this->assertSame(
            ResponseCode::SUCCESS(),
            $response->code()
        );

        $this->assertSame(
            '<return-value>',
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

    public function testExtract()
    {
        $response = Response::createFromValue('<return-value>');

        $this->assertSame(
            '<return-value>',
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

    public function extractTestVectors()
    {
        return [
            [new LogicException('The exception message.'), ExecutionException::class],
            [new InvalidMessageException('The exception message.')],
            [new UnknownProcedureException('procedure-name')],
        ];
    }

    public function testToString()
    {
        $response = Response::createFromValue('<return-value>');

        $this->assertSame(
            '"<return-value>"',
            strval($response)
        );
    }

    public function testToStringWithException()
    {
        $exception = new Exception('Error message!');

        $response = Response::createFromException($exception);

        $this->assertSame(
            'EXCEPTION (Error message!)',
            strval($response)
        );
    }
}
