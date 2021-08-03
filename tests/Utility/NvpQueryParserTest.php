<?php

namespace Tests\Utility;

use Dormilich\HttpClient\Exception\EncoderException;
use Dormilich\HttpClient\Utility\NvpQueryParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Utility\NvpQueryParser
 */
class NvpQueryParserTest extends TestCase
{
    private function toObject(array $data): \stdClass
    {
        $object = new \stdClass();

        foreach ($data as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    public function testEncodeEmptyArray()
    {
        $service = new NvpQueryParser();
        $query = $service->encode([]);

        $this->assertSame('', $query);
    }

    public function testDecodeEmptyQuery()
    {
        $service = new NvpQueryParser();
        $data = $service->decode('');

        $this->assertEquals(new \stdClass(), $data);
    }

    public function testEncodeAssociativeArray()
    {
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new NvpQueryParser();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testEncodeIterable()
    {
        $data = new \ArrayObject();
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new NvpQueryParser();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testDecodeObject()
    {
        $service = new NvpQueryParser();
        $data = $service->decode('foo=bar&xxx=1');

        $exp['foo'] = 'bar';
        $exp['xxx'] = '1';
        $this->assertEquals($this->toObject($exp), $data);
    }

    public function testEncodeList()
    {
        $data['xxx'] = ['foo', 'bar'];

        $service = new NvpQueryParser();
        $query = $service->encode($data);

        $this->assertSame('xxx=foo&xxx=bar', $query);
    }

    public function testDecodeList()
    {
        $service = new NvpQueryParser();
        $data = $service->decode('xxx=foo&xxx=bar&xxx=baz');

        $exp['xxx'] = ['foo', 'bar', 'baz'];
        $this->assertEquals($this->toObject($exp), $data);
    }

    public function testEncodeNestedArrayFails()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Value for parameter \'foo\' cannot be encoded, a non-hash value is expected.');

        $data['foo']['bar'] = 1;

        $service = new NvpQueryParser();
        $service->encode($data);
    }

    public function testEncodeObjectFails()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Value for parameter \'foo\' cannot be encoded, a scalar value is expected.');

        $data['foo'] = new \stdClass();

        $service = new NvpQueryParser();
        $service->encode($data);
    }

    public function testEncodeEmptyParameter()
    {
        $data['foo'] = 'bar';
        $data['xxx'] = null;

        $service = new NvpQueryParser();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx', $query);
    }

    public function testDecodeEmptyParameter()
    {
        $service = new NvpQueryParser();
        $data = $service->decode('foo=bar&xxx');

        $exp['foo'] = 'bar';
        $exp['xxx'] = null;
        $this->assertEquals($this->toObject($exp), $data);
    }

    public function testEncodeBoolean()
    {
        $data['foo'] = true;
        $data['bar'] = false;

        $service = new NvpQueryParser();
        $query = $service->encode($data);

        $this->assertSame('foo=true&bar=false', $query);
    }
}
