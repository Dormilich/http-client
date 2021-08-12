<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Transformer\JsonDecoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\JsonDecoder
 */
class JsonDecoderTest extends TestCase
{
    public function testContentTypeIsJson()
    {
        $transformer = new JsonDecoder();
        $result = $transformer->contentType();

        $this->assertSame('application/json', $result);
    }

    public function testDecodeJson()
    {
        $transformer = new JsonDecoder();
        $result = $transformer->decode('"test"');

        $this->assertSame('test', $result);
    }

    public function testDecodeJsonAsObject()
    {
        $transformer = new JsonDecoder();
        $result = $transformer->decode('{"foo":"bar"}');

        $this->assertIsObject($result);
        $this->assertObjectHasAttribute('foo', $result);
        $this->assertSame('bar', $result->foo);
    }

    public function testDecodeJsonAsArray()
    {
        $transformer = new JsonDecoder(JSON_OBJECT_AS_ARRAY);
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

        $transformer = new JsonDecoder();
        $transformer->decode('test');
    }
}
