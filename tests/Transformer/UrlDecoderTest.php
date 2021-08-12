<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Transformer\UrlDecoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\UrlDecoder
 * @uses \Dormilich\HttpClient\Utility\PhpQuery
 */
class UrlDecoderTest extends TestCase
{
    public function testContentTypeIsForm()
    {
        $transformer = new UrlDecoder();
        $result = $transformer->contentType();

        $this->assertSame('application/x-www-form-urlencoded', $result);
    }

    public function testDecodeAsArray()
    {
        $transformer = new UrlDecoder();
        $result = $transformer->decode('foo=1&bar=2');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('foo', $result);
        $this->assertSame('1', $result['foo']);
        $this->assertArrayHasKey('bar', $result);
        $this->assertSame('2', $result['bar']);
    }
}
