<?php

namespace Tests\Encoder;

use Dormilich\HttpClient\Encoder\JsonEncoder;
use Dormilich\HttpClient\Exception\EncoderException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Encoder\ContentTrait
 * @covers \Dormilich\HttpClient\Encoder\JsonEncoder
 */
class JsonEncoderTest extends TestCase
{
    public function testContentType()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new JsonEncoder($factory);

        $this->assertSame('application/json', $encoder->getContentType());
    }

    public function testEncoderSupportsPlainObjects()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new JsonEncoder($factory);

        $object = new \stdClass();

        $this->assertTrue($encoder->supports($object));
    }

    /**
     * @depends testEncoderSupportsPlainObjects
     */
    public function testEncodePlainObject()
    {
        $object = new \stdClass();
        $object->foo = 'bar';

        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo('{"foo":"bar"}'))
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
                $this->identicalTo('application/json')
            )
            ->willReturnSelf();

        $encoder = new JsonEncoder($factory);
        $encoder->serialize($request, $object);
    }

    public function testEncoderSupportsJsonObjects()
    {
        $factory = $this->createStub(StreamFactoryInterface::class);
        $encoder = new JsonEncoder($factory);

        $object = $this->createStub(\JsonSerializable::class);

        $this->assertTrue($encoder->supports($object));
    }

    /**
     * @depends testEncoderSupportsJsonObjects
     */
    public function testEncodeJsonObject()
    {
        $object = $this->createStub(\JsonSerializable::class);
        $object
            ->method('jsonSerialize')
            ->willReturn(['foo' => 'bar']);

        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo('{"foo":"bar"}'))
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
                $this->identicalTo('application/json')
            )
            ->willReturnSelf();

        $encoder = new JsonEncoder($factory);
        $encoder->serialize($request, $object);
    }

    public function testEncodingError()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

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

        $encoder = new JsonEncoder($factory);
        $encoder->serialize($request, NAN); // Not A Number
    }

    /**
     * JSON_NUMERIC_CHECK = 32
     *
     * @testWith ["3.14", 32, "3.14"]
     *           ["3.14",  0, "\"3.14\""]
     */
    public function testUseJsonOptions($value, int $option, string $result)
    {
        $stream = $this->createStub(StreamInterface::class);
        $factory = $this->createMock(StreamFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createStream')
            ->with($this->identicalTo($result))
            ->willReturn($stream);
        $request = $this->createStub(RequestInterface::class);
        $request
            ->method('withBody')
            ->willReturnSelf();
        $request
            ->method('withHeader')
            ->willReturnSelf();

        $encoder = new JsonEncoder($factory, $option);
        $encoder->serialize($request, $value);
    }
}
