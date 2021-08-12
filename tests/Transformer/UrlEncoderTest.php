<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Transformer\UrlEncoder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\UrlEncoder
 * @uses \Dormilich\HttpClient\Utility\PhpQuery
 */
class UrlEncoderTest extends TestCase
{
    public function testContentTypeIsForm()
    {
        $transformer = new UrlEncoder();
        $result = $transformer->contentType();

        $this->assertSame('application/x-www-form-urlencoded', $result);
    }

    public function testEncoderSupportsArrays()
    {
        $data['foo'] = 1;
        $data['bar'] = 2;

        $transformer = new UrlEncoder();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testEncoderSupportsArrays
     */
    public function testEncodeArray(array $data)
    {
        $transformer = new UrlEncoder();
        $result = $transformer->encode($data);

        $this->assertSame('foo=1&bar=2', $result);
    }
}
