<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Exception\EncoderException;
use Dormilich\HttpClient\Transformer\JsonEncoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\JsonEncoder
 */
class JsonEncoderTest extends TestCase
{
    public function testContentTypeIsJson()
    {
        $transformer = new JsonEncoder();
        $result = $transformer->contentType();

        $this->assertSame('application/json', $result);
    }

    public function testEncoderSupportsPlainObjects()
    {
        $data = new \stdClass();
        $data->foo = 'bar';

        $transformer = new JsonEncoder();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testEncoderSupportsPlainObjects
     */
    public function testEncodePlainObject(\stdClass $data)
    {
        $transformer = new JsonEncoder();
        $result = $transformer->encode($data);

        $this->assertSame('{"foo":"bar"}', $result);
    }

    public function testEncoderSupportsJsonObjects()
    {
        $data = $this->createStub(\JsonSerializable::class);
        $data
            ->method('jsonSerialize')
            ->willReturn(['foo' => 'bar']);

        $transformer = new JsonEncoder();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testEncoderSupportsJsonObjects
     */
    public function testEncodeJsonObject(\JsonSerializable $data)
    {
        $transformer = new JsonEncoder();
        $result = $transformer->encode($data);

        $this->assertSame('{"foo":"bar"}', $result);
    }

    public function testEncodingError()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

        $transformer = new JsonEncoder();
        $transformer->encode(NAN); // Not A Number
    }

    /**
     * JSON_NUMERIC_CHECK = 32
     *
     * @testWith ["3.14", 32, "3.14"]
     *           ["3.14",  0, "\"3.14\""]
     */
    public function testUseJsonOptions($value, int $option, string $expected)
    {
        $transformer = new JsonEncoder($option);
        $result = $transformer->encode($value);

        $this->assertSame($expected, $result);
    }
}
