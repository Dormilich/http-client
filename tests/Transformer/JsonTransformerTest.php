<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Exception\EncoderException;
use Dormilich\HttpClient\Transformer\JsonTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\JsonTransformer
 */
class JsonTransformerTest extends TestCase
{
    public function testContentTypeIsJson()
    {
        $transformer = new JsonTransformer();
        $result = $transformer->contentType();

        $this->assertSame('application/json', $result);
    }

    public function testTransformerSupportsPlainObjects()
    {
        $data = new \stdClass();
        $data->foo = 'bar';

        $transformer = new JsonTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testTransformerSupportsPlainObjects
     */
    public function testEncodePlainObject(\stdClass $data)
    {
        $transformer = new JsonTransformer();
        $result = $transformer->encode($data);

        $this->assertSame('{"foo":"bar"}', $result);
    }

    public function testTransformerSupportsJsonObjects()
    {
        $data = $this->createStub(\JsonSerializable::class);
        $data
            ->method('jsonSerialize')
            ->willReturn(['foo' => 'bar']);

        $transformer = new JsonTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testTransformerSupportsJsonObjects
     */
    public function testEncodeJsonObject(\JsonSerializable $data)
    {
        $transformer = new JsonTransformer();
        $result = $transformer->encode($data);

        $this->assertSame('{"foo":"bar"}', $result);
    }

    public function testEncodingError()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

        $transformer = new JsonTransformer();
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
        $transformer = new JsonTransformer($option);
        $result = $transformer->encode($value);

        $this->assertSame($expected, $result);
    }

    public function testDecodeJson()
    {
        $transformer = new JsonTransformer();
        $result = $transformer->decode('"test"');

        $this->assertSame('test', $result);
    }

    public function testDecodeJsonAsObject()
    {
        $transformer = new JsonTransformer();
        $result = $transformer->decode('{"foo":"bar"}');

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('foo', $result);
        $this->assertSame('bar', $result->foo);
    }

    public function testDecodeJsonAsArray()
    {
        $transformer = new JsonTransformer(JSON_OBJECT_AS_ARRAY);
        $result = $transformer->decode('{"foo":"bar"}');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('foo', $result);
        $this->assertSame('bar', $result['foo']);
    }

    public function testDecodeInvalidJsonFails()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionCode(JSON_ERROR_SYNTAX);
        $this->expectExceptionMessage('Syntax error');

        $transformer = new JsonTransformer();
        $transformer->decode('test');
    }
}
