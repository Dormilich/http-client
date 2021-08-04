<?php

namespace Tests\Decoder;

use Dormilich\HttpClient\Decoder\DomDecoder;
use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Utility\StatusMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Decoder\ContentTypeTrait
 * @covers \Dormilich\HttpClient\Decoder\DomDecoder
 * @covers \Dormilich\HttpClient\Decoder\StatusCodeTrait
 * @uses \Dormilich\HttpClient\Utility\StatusMatcher
 * @uses \Dormilich\HttpClient\Utility\Header
 */
class DomDecoderTest extends TestCase
{
    public function testDecoderHasXmlType()
    {
        $decoder = new DomDecoder();

        $this->assertSame('application/xml', $decoder->getContentType());
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
            ->willReturn('application/xml');

        $decoder = new DomDecoder();

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
            ->willReturn('application/xml');

        $decoder = new DomDecoder();
        $decoder->setStatusMatcher(StatusMatcher::success());

        $this->assertSame($isMatch, $decoder->supports($response));
    }

    /**
     * @testWith ["application/xml"]
     *           ["application/xhtml+xml"]
     *           ["application/xml; charset=UTF-16"]
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

        $decoder = new DomDecoder();

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

        $decoder = new DomDecoder();

        $this->assertFalse($decoder->supports($response));
    }

    public function testDecodeXml()
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>";

        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn($xml);
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new DomDecoder();
        $result = $decoder->unserialize($response);

        $this->assertInstanceOf(\DOMDocument::class, $result);
        $this->assertSame('root', $result->documentElement->tagName);
        $this->assertSame('test', $result->documentElement->textContent);
    }

    public function testDecodeInvalidXmlFails()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionCode(E_WARNING);
        $this->expectExceptionMessage('Start tag expected');

        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('test');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new DomDecoder();
        $decoder->unserialize($response);
    }
}
