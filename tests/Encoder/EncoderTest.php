<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\Encoder;
use Dormilich\HttpClient\Exception\EncoderException;
use Dormilich\HttpClient\Transformer\DataEncoderInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\Encoder
 */
class EncoderTest extends TestCase
{
    public function testEncoderContentType()
    {
        $type = uniqid();

        $factory = $this->createStub(StreamFactoryInterface::class);
        $transformer = $this->createConfiguredMock(DataEncoderInterface::class, [
            'contentType' => $type,
        ]);
        
        $encoder = new Encoder($factory, $transformer);
        
        $this->assertSame($type, $encoder->getContentType());
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function testEncoderSupportsData(bool $boolean)
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $transformer = $this->createConfiguredMock(DataEncoderInterface::class, [
            'supports' => $boolean,
        ]);

        $encoder = new Encoder($factory, $transformer);
        
        $this->assertSame($boolean, $encoder->supports(null));
    }

    /**
     * @testWith ["POST"]
     *           ["PATCH"]
     *           ["PUT"]
     *           ["DELETE"]
     */
    public function testEncodeBodyData(string $method)
    {
        $data = new \stdClass();
        $content = uniqid();

        $transformer = $this->createMock(DataEncoderInterface::class);
        $transformer
            ->method('contentType')
            ->willReturn('text/plain');
        $transformer
            ->expects($this->once())
            ->method('encode')
            ->with($this->identicalTo($data))
            ->willReturn($content);

        $stream = $this->createStub(StreamInterface::class);

        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo($content))
            ->willReturn($stream);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn($method);
        $request
            ->expects($this->never())
            ->method('withUri');
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
                $this->identicalTo('text/plain')
            )
            ->willReturnSelf();

        $encoder = new Encoder($factory, $transformer);
        $encoder->serialize($request, $data);
    }

    /**
     * @testWith ["GET"]
     *           ["HEAD"]
     *           ["TRACE"]
     */
    public function testEncodeQueryData(string $method)
    {
        $data = new \stdClass();
        $content = uniqid();

        $transformer = $this->createMock(DataEncoderInterface::class);
        $transformer
            ->method('contentType')
            ->willReturn('text/plain');
        $transformer
            ->expects($this->once())
            ->method('encode')
            ->with($this->identicalTo($data))
            ->willReturn($content);

        $factory = $this->createStub(StreamFactoryInterface::class);

        $uri = $this->createMock(UriInterface::class);
        $uri
            ->expects($this->once())
            ->method('withQuery')
            ->with($this->identicalTo($content))
            ->willReturnSelf();

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn($method);
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->method('getUri')
            ->willReturn($uri);
        $request
            ->expects($this->once())
            ->method('withUri')
            ->with($this->identicalTo($uri))
            ->willReturnSelf();

        $encoder = new Encoder($factory, $transformer);
        $encoder->serialize($request, $data);
    }

    /**
     * @testWith ["CONNECT"]
     *           ["OPTIONS"]
     */
    public function testIgnoreData(string $method)
    {
        $data = new \stdClass();
        $content = uniqid();

        $transformer = $this->createConfiguredMock(DataEncoderInterface::class, [
            'contentType' => 'text/plain',
            'encode' => $content,
        ]);

        $factory = $this->createStub(StreamFactoryInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn($method);
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->expects($this->never())
            ->method('withUri');

        $encoder = new Encoder($factory, $transformer);
        $encoder->serialize($request, $data);
    }

    /**
     * @testWith ["GET"]
     *           ["POST"]
     */
    public function testIgnoreEmptyData(string $method)
    {
        $data = new \stdClass();

        $transformer = $this->createConfiguredMock(DataEncoderInterface::class, [
            'contentType' => 'text/plain',
            'encode' => '',
        ]);

        $factory = $this->createStub(StreamFactoryInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('getMethod')
            ->willReturn($method);
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->expects($this->never())
            ->method('withUri');

        $encoder = new Encoder($factory, $transformer);
        $encoder->serialize($request, $data);
    }

    public function testEncodingError()
    {
        $this->expectException(EncoderException::class);

        $transformer = $this->createMock(DataEncoderInterface::class);
        $transformer
            ->method('contentType')
            ->willReturn('text/plain');
        $transformer
            ->expects($this->once())
            ->method('encode')
            ->willThrowException(new EncoderException('test'));

        $factory = $this->createStub(StreamFactoryInterface::class);

        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');
        $request
            ->expects($this->never())
            ->method('withUri');

        $encoder = new Encoder($factory, $transformer);
        $encoder->serialize($request, null);
    }
}
