<?php

namespace Tests\Decoder;

use Dormilich\HttpClient\Decoder\Decoder;
use Dormilich\HttpClient\Transformer\DataDecoderInterface;
use Dormilich\HttpClient\Utility\StatusMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Decoder\ContentTypeTrait
 * @covers \Dormilich\HttpClient\Decoder\Decoder
 * @covers \Dormilich\HttpClient\Decoder\StatusCodeTrait
 * @uses \Dormilich\HttpClient\Utility\StatusMatcher
 */
class DecoderTest extends TestCase
{
    public function testEncoderContentType()
    {
        $type = uniqid();

        $transformer = $this->createConfiguredMock(DataDecoderInterface::class, [
            'contentType' => $type,
        ]);

        $decoder = new Decoder($transformer);

        $this->assertSame($type, $decoder->getContentType());
    }

    /**
     * @testWith [200]
     *           [300]
     *           [400]
     *           [500]
     */
    public function testDecoderMatchesAnyStatusWithoutMatcher(int $code)
    {
        $type = uniqid();

        $transformer = $this->createConfiguredMock(DataDecoderInterface::class, [
            'contentType' => $type,
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => $code,
            'getHeaderLine' => $type,
        ]);

        $decoder = new Decoder($transformer);

        $this->assertTrue($decoder->supports($response));
    }

    /**
     * @testWith [200, true]
     *           [400, false]
     */
    public function testDecoderMatchesDefinedStatusCodes(int $code, bool $isMatch)
    {
        $type = uniqid();

        $transformer = $this->createConfiguredMock(DataDecoderInterface::class, [
            'contentType' => $type,
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => $code,
            'getHeaderLine' => $type,
        ]);

        $decoder = new Decoder($transformer);
        $decoder->setStatusMatcher(StatusMatcher::success());

        $this->assertSame($isMatch, $decoder->supports($response));
    }

    /**
     * @testWith ["application/json"]
     *           ["application/vnd.api+json"]
     */
    public function testDecoderMatchesContentTypes(string $type)
    {
        $transformer = $this->createConfiguredMock(DataDecoderInterface::class, [
            'contentType' => 'application/json',
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => 200,
            'getHeaderLine' => $type,
        ]);

        $decoder = new Decoder($transformer);

        $this->assertTrue($decoder->supports($response));
    }

    public function testDecoderIgnoresInvalidContentType()
    {
        $transformer = $this->createConfiguredMock(DataDecoderInterface::class, [
            'contentType' => 'application/json',
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => 200,
            'getHeaderLine' => 'application/octet-stream',
        ]);

        $decoder = new Decoder($transformer);

        $this->assertFalse($decoder->supports($response));
    }

    public function testDecodeResponse()
    {
        $data = new \stdClass();
        $content = uniqid();

        $stream = $this->createConfiguredMock(StreamInterface::class, [
            '__toString' => $content,
        ]);
        $response = $this->createConfiguredMock(ResponseInterface::class, [
            'getBody' => $stream,
        ]);
        $transformer = $this->createMock(DataDecoderInterface::class);
        $transformer
            ->expects($this->once())
            ->method('decode')
            ->with($this->identicalTo($content))
            ->willReturn($data);

        $decoder = new Decoder($transformer);
        $result = $decoder->unserialize($response);

        $this->assertSame($data, $result);
    }
}
