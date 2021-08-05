<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Transformer\UrlTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\UrlTransformer
 * @uses \Dormilich\HttpClient\Utility\PhpQueryParser
 */
class UrlTransformerTest extends TestCase
{
    public function testContentTypeIsForm()
    {
        $transformer = new UrlTransformer();
        $result = $transformer->contentType();

        $this->assertSame('application/x-www-form-urlencoded', $result);
    }

    public function testTransformerSupportsArrays()
    {
        $data['foo'] = 1;
        $data['bar'] = 2;

        $transformer = new UrlTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testTransformerSupportsArrays
     */
    public function testEncodeArray(array $data)
    {
        $transformer = new UrlTransformer();
        $result = $transformer->encode($data);

        $this->assertSame('foo=1&bar=2', $result);
    }

    public function testDecodeAsArray()
    {
        $transformer = new UrlTransformer();
        $result = $transformer->decode('foo=1&bar=2');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('foo', $result);
        $this->assertSame('1', $result['foo']);
        $this->assertArrayHasKey('bar', $result);
        $this->assertSame('2', $result['bar']);
    }
}
