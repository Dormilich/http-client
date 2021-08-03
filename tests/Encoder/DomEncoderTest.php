<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\DomEncoder;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\DomEncoder
 */
class DomEncoderTest extends TestCase
{
    public function testContentType()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new DomEncoder($factory);

        $this->assertSame('application/xml', $encoder->getContentType());
    }

    public function testEncoderSupportsDocument()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new DomEncoder($factory);

        $object = new \DOMDocument();

        $this->assertTrue($encoder->supports($object));
    }

    /**
     * @depends testEncoderSupportsDocument
     */
    public function testEncodeDocument()
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>";
        $object = $this->createStub(\DOMDocument::class);
        $object
            ->method('saveXML')
            ->willReturn($xml);

        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo($xml))
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
                $this->identicalTo('application/xml')
            )
            ->willReturnSelf();

        $encoder = new DomEncoder($factory);
        $encoder->serialize($request, $object);
    }
}
