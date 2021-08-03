<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\UrlEncoder;
use Dormilich\HttpClient\Utility\QueryParser;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\UrlEncoder
 */
class UrlEncoderTest extends TestCase
{
    public function testContentType()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $parser = $this->createStub(QueryParser::class);
        $encoder = new UrlEncoder($factory, $parser);

        $this->assertSame('application/x-www-form-urlencoded', $encoder->getContentType());
    }

    public function testEncoderSupportsArray()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $parser = $this->createStub(QueryParser::class);
        $encoder = new UrlEncoder($factory, $parser);

        $this->assertTrue($encoder->supports([]));
    }

    public function testEncodeArrayInBody()
    {
        $data['foo'] = 'bar';

        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo('foo=bar'))
            ->willReturn($stream);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');
        $request
            ->expects($this->once())
            ->method('withBody')
            ->with($this->identicalTo($stream))
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->with(
                $this->identicalTo('Content-Type'),
                $this->identicalTo('application/x-www-form-urlencoded')
            )
            ->willReturnSelf();
        $parser = $this->createStub(QueryParser::class);
        $parser
            ->method('encode')
            ->willReturnCallback('http_build_query');

        $encoder = new UrlEncoder($factory, $parser);
        $encoder->serialize($request, $data);
    }

    public function testEncodeArrayInUrl()
    {
        $data['foo'] = 'bar';

        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->never())
            ->method('createStream');
        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with($this->identicalTo('foo=bar'))
            ->willReturnSelf();
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('GET');
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->expects($this->once())
            ->method('getUri')
            ->willReturn($uri);
        $request
            ->expects($this->once())
            ->method('withUri')
            ->with($this->identicalTo($uri))
            ->willReturnSelf();
        $parser = $this->createStub(QueryParser::class);
        $parser
            ->method('encode')
            ->willReturnCallback('http_build_query');

        $encoder = new UrlEncoder($factory, $parser);
        $encoder->serialize($request, $data);
    }
}
