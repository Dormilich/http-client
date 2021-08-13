<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\ContentLength;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\ContentLength
 */
class ContentLengthTest extends TestCase
{
    public function testEncoderDoesNotHaveType()
    {
        $encoder = new ContentLength();

        $this->assertNull($encoder->getContentType());
    }

    public function testEncoderIgnoresChunkedRequest()
    {
        $request = $this->createStub(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn('POST');
        $request
            ->method('hasHeader')
            ->willReturnMap([['Transfer-Encoding', true]]);

        $encoder = new ContentLength();

        $this->assertFalse($encoder->supports($request));
    }

    public function testAddContentLength()
    {
        $stream = $this->createConfiguredMock(StreamInterface::class, [
            'getSize' => 12345,
        ]);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getBody')
            ->willReturn($stream);
        $request
            ->expects($this->once())
            ->method('withHeader')
            ->with(
                $this->identicalTo('Content-Length'),
                $this->identicalTo(12345)
            )
            ->willReturnSelf();

        $encoder = new ContentLength();
        $encoder->serialize($request, null);
    }

    public function testIgnoreEmptyContentLength()
    {
        $stream = $this->createConfiguredMock(StreamInterface::class, [
            'getSize' => 0,
        ]);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getBody')
            ->willReturn($stream);
        $request
            ->expects($this->never())
            ->method('withHeader');

        $encoder = new ContentLength();
        $encoder->serialize($request, null);
    }
}
