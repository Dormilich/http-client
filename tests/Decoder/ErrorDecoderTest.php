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
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => $code,
        ]);

        $decoder = new ErrorDecoder();

        $this->assertTrue($decoder->supports($response));
    }

    public function testDecoderIgnoresSuccessResponse()
    {
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => 200,
        ]);

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

        $stream = $this->createConfiguredMock(StreamInterface::class, [
            '__toString' => 'this did not work',
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => 400,
            'getBody' => $stream,
        ]);

        $decoder = new ErrorDecoder();
        $decoder->unserialize($response);
    }
}
