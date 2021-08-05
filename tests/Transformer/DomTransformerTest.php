<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Transformer\DomTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\DomTransformer
 */
class DomTransformerTest extends TestCase
{
    public function testContentTypeIsXml()
    {
        $transformer = new DomTransformer();
        $result = $transformer->contentType();

        $this->assertSame('application/xml', $result);
    }

    public function testTransformerSupportsDocument()
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>";
        $data = new \DOMDocument();
        $data->loadXML($xml);

        $transformer = new DomTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testTransformerSupportsDocument
     */
    public function testEncodeDocument(\DOMDocument $data)
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>\n";

        $transformer = new DomTransformer();
        $result = $transformer->encode($data);

        $this->assertSame($xml, $result);

        return $result;
    }

    /**
     * @depends testEncodeDocument
     */
    public function testDecodeXml(string $data)
    {
        $transformer = new DomTransformer();
        $result = $transformer->decode($data);

        $this->assertInstanceOf(\DOMDocument::class, $result);
        $this->assertSame('root', $result->documentElement->tagName);
        $this->assertSame('test', $result->documentElement->textContent);
    }

    public function testDecodeInvalidXmlFails()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionCode(E_WARNING);
        $this->expectExceptionMessage('Start tag expected');

        $transformer = new DomTransformer();
        $transformer->decode('test');
    }
}
