<?php

namespace Tests\Decoder;

use Dormilich\HttpClient\Decoder\ErrorDecoder;
use Dormilich\HttpClient\Exception\RequestException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Decoder\ErrorDecoder
 */
class ErrorDecoderTest extends TestCase
{
    /**
     * @testWith [400]
     *           [401]
     *           [418]
     *           [500]
     */
    public function testDecoderSupportsErrorResponse(int $code)
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn($code);

        $decoder = new ErrorDecoder();

        $this->assertTrue($decoder->supports($response));
    }

    public function testDecoderIgnoresSuccessResponse()
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn(200);

        $decoder = new ErrorDecoder();

        $this->assertFalse($decoder->supports($response));
    }

    public function testDecoderHasGenericType()
    {
        $decoder = new ErrorDecoder();

        $this->assertSame('*/*', $decoder->getContentType());
    }

    public function testDecoderThrowsException()
    {
        $this->expectException(RequestException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('this did not work');

        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('this did not work');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn(400);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new ErrorDecoder();
        $decoder->unserialize($response);
    }
}
