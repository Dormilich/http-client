<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\TextEncoder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\TextEncoder
 */
class TextEncoderTest extends TestCase
{
    public function testContentType()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new TextEncoder($factory);

        $this->assertSame('text/plain', $encoder->getContentType());
    }

    /**
     * @testWith [42]
     *           [3.14]
     *           ["123.45"]
     */
    public function testEncoderSupportsNumbers($value)
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new TextEncoder($factory);

        $this->assertTrue($encoder->supports($value));
    }

    public function testEncoderSupportsStrings()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new TextEncoder($factory);

        $this->assertTrue($encoder->supports('foo'));
    }

    public function testEncoderSupportsStringObjects()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new TextEncoder($factory);

        $object = $this->getMockBuilder('stdClass')
            ->addMethods(['__toString'])
            ->getMock();

        $this->assertTrue($encoder->supports($object));
    }

    public function testEncodeString()
    {
        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo('foo'))
            ->willReturn($stream);
        $request = $this->createMock(RequestInterface::class);
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

        $encoder = new TextEncoder($factory);
        $encoder->serialize($request, 'foo');
    }

    public function testEncodeEmptyString()
    {
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->never())
            ->method('createStream');
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->never())
            ->method('withBody');
        $request
            ->expects($this->never())
            ->method('withHeader');

        $encoder = new TextEncoder($factory);
        $encoder->serialize($request, '');
    }
}
