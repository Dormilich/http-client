<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Transformer\TextTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\TextTransformer
 */
class TextTransformerTest extends TestCase
{
    private function stringObject(string $data)
    {
        $mock = $this->getMockBuilder('stdClass')
            ->addMethods(['__toString'])
            ->getMock();
        $mock
            ->method('__toString')
            ->willReturn($data);

        return $mock;
    }

    public function testContentTypeIsText()
    {
        $transformer = new TextTransformer();
        $result = $transformer->contentType();

        $this->assertSame('text/plain', $result);
    }

    public function testTransformerSupportsStrings()
    {
        $data = uniqid();

        $transformer = new TextTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);
    }

    /**
     * @testWith [42]
     *           [3.14]
     *           ["123.45"]
     */
    public function testTransformerSupportsNumbers($data)
    {
        $transformer = new TextTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);
    }

    public function testTransformerSupportsStringObjects()
    {
        $data = $this->stringObject('test');

        $transformer = new TextTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);
    }

    public function testEncodeString()
    {
        $data = uniqid();

        $transformer = new TextTransformer();
        $result = $transformer->encode($data);

        $this->assertSame($data, $result);
    }

    public function testEncodeStringObject()
    {
        $text = uniqid();
        $data = $this->stringObject($text);

        $transformer = new TextTransformer();
        $result = $transformer->encode($data);

        $this->assertSame($text, $result);
    }

    public function testDecodeString()
    {
        $data = uniqid();

        $transformer = new TextTransformer();
        $result = $transformer->decode($data);

        $this->assertSame($data, $result);
    }
}
