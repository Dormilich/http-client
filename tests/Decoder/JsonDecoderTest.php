<?php

namespace Tests\Decoder;

use Dormilich\HttpClient\Decoder\JsonDecoder;
use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Utility\StatusMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Decoder\ContentTypeTrait
 * @covers \Dormilich\HttpClient\Decoder\JsonDecoder
 * @covers \Dormilich\HttpClient\Decoder\StatusCodeTrait
 * @uses \Dormilich\HttpClient\Utility\StatusMatcher
 */
class JsonDecoderTest extends TestCase
{
    public function testDecoderHasJsonType()
    {
        $decoder = new JsonDecoder();

        $this->assertSame('application/json', $decoder->getContentType());
    }

    /**
     * @testWith [200]
     *           [300]
     *           [400]
     *           [500]
     */
    public function testDecoderMatchesAnyStatusWithoutMatcher(int $code)
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn($code);
        $response
            ->method('getHeaderLine')
            ->willReturn('application/json');

        $decoder = new JsonDecoder();

        $this->assertTrue($decoder->supports($response));
    }

    /**
     * @testWith [200, true]
     *           [400, false]
     */
    public function testDecoderMatchesDefinedStatusCodes(int $code, bool $isMatch)
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn($code);
        $response
            ->method('getHeaderLine')
            ->willReturn('application/json');

        $decoder = new JsonDecoder();
        $decoder->setStatusMatcher(StatusMatcher::success());

        $this->assertSame($isMatch, $decoder->supports($response));
    }

    /**
     * @testWith ["application/json"]
     *           ["application/json-seq"]
     *           ["application/jwk+json"]
     *           ["application/vnd.api+json"]
     *           ["application/json; charset=UTF-16"]
     */
    public function testDecoderMatchesContentTypes(string $type)
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn(200);
        $response
            ->method('getHeaderLine')
            ->willReturn($type);

        $decoder = new JsonDecoder();

        $this->assertTrue($decoder->supports($response));
    }

    public function testDecoderIgnoresInvalidContentType()
    {
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getStatusCode')
            ->willReturn(200);
        $response
            ->method('getHeaderLine')
            ->willReturn('application/octet-stream');

        $decoder = new JsonDecoder();

        $this->assertFalse($decoder->supports($response));
    }

    public function testDecodeJson()
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('"test"');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new JsonDecoder();
        $result = $decoder->unserialize($response);

        $this->assertSame('test', $result);
    }

    public function testDecodeJsonAsObject()
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('{"foo":"bar"}');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new JsonDecoder();
        $result = $decoder->unserialize($response);

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('foo', $result);
    }

    public function testDecodeJsonAsArray()
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('{"foo":"bar"}');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new JsonDecoder(JSON_OBJECT_AS_ARRAY);
        $result = $decoder->unserialize($response);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('foo', $result);
    }

    public function testDecodeInvalidJsonFails()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionCode(JSON_ERROR_SYNTAX);
        $this->expectExceptionMessage('Syntax error');

        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('test');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new JsonDecoder(JSON_OBJECT_AS_ARRAY);
        $decoder->unserialize($response);
    }
}
