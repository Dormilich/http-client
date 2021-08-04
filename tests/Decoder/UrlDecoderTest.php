<?php

namespace Tests\Decoder;

use Dormilich\HttpClient\Decoder\UrlDecoder;
use Dormilich\HttpClient\Utility\PhpQueryParser;
use Dormilich\HttpClient\Utility\QueryParser;
use Dormilich\HttpClient\Utility\StatusMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Decoder\ContentTypeTrait
 * @covers \Dormilich\HttpClient\Decoder\UrlDecoder
 * @covers \Dormilich\HttpClient\Decoder\StatusCodeTrait
 * @uses \Dormilich\HttpClient\Utility\StatusMatcher
 * @uses \Dormilich\HttpClient\Utility\PhpQueryParser
 */
class UrlDecoderTest extends TestCase
{
    public function testDecoderHasFormType()
    {
        $parser = $this->createStub(QueryParser::class);
        $decoder = new UrlDecoder($parser);

        $this->assertSame('application/x-www-form-urlencoded', $decoder->getContentType());
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
            ->willReturn('application/x-www-form-urlencoded');

        $parser = $this->createStub(QueryParser::class);
        $decoder = new UrlDecoder($parser);

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
            ->willReturn('application/x-www-form-urlencoded');

        $parser = $this->createStub(QueryParser::class);
        $decoder = new UrlDecoder($parser);
        $decoder->setStatusMatcher(StatusMatcher::success());

        $this->assertSame($isMatch, $decoder->supports($response));
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

        $parser = $this->createStub(QueryParser::class);
        $decoder = new UrlDecoder($parser);

        $this->assertFalse($decoder->supports($response));
    }

    public function testDecodeFormData()
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream
            ->method('__toString')
            ->willReturn('foo=1&bar=2');
        $response = $this->createStub(ResponseInterface::class);
        $response
            ->method('getBody')
            ->willReturn($stream);

        $decoder = new UrlDecoder(new PhpQueryParser());
        $result = $decoder->unserialize($response);

        $this->assertIsArray($result);
        $this->assertEquals(['foo' => '1', 'bar' => 2], $result);
    }
}
