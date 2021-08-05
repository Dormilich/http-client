<?php

namespace Tests\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Transformer\XmlTransformer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Transformer\XmlTransformer
 */
class XmlTransformerTest extends TestCase
{
    public function testContentTypeIsXml()
    {
        $transformer = new XmlTransformer();
        $result = $transformer->contentType();

        $this->assertSame('application/xml', $result);
    }

    public function testTransformerSupportsSimpleXml()
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>";
        $data = simplexml_load_string($xml);

        $transformer = new XmlTransformer();
        $result = $transformer->supports($data);

        $this->assertTrue($result);

        return $data;
    }

    /**
     * @depends testTransformerSupportsSimpleXml
     */
    public function testEncodeSimpleXml(\SimpleXMLElement $data)
    {
        $xml = "<?xml version=\"1.0\"?>\n<root>test</root>\n";

        $transformer = new XmlTransformer();
        $result = $transformer->encode($data);

        $this->assertSame($xml, $result);

        return $result;
    }

    /**
     * @depends testEncodeSimpleXml
     */
    public function testDecodeXml(string $data)
    {
        $transformer = new XmlTransformer();
        $result = $transformer->decode($data);

        $this->assertInstanceOf(\SimpleXMLElement::class, $result);
        $this->assertSame('test', (string) $result);
    }

    public function testDecodeInvalidXmlFails()
    {
        $this->expectException(DecoderException::class);
        $this->expectExceptionCode(E_WARNING);
        $this->expectExceptionMessage('Start tag expected');

        $transformer = new XmlTransformer();
        $transformer->decode('test');
    }
}
