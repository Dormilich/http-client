<?php

namespace Tests\Utility;

use Dormilich\HttpClient\Exception\EncoderException;
use Dormilich\HttpClient\Utility\NvpQuery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Utility\NvpQuery
 */
class NvpQueryTest extends TestCase
{
    public function testEncodeEmptyArray()
    {
        $service = new NvpQuery();
        $query = $service->encode([]);

        $this->assertSame('', $query);
    }

    public function testDecodeEmptyQuery()
    {
        $service = new NvpQuery();
        $data = $service->decode('');

        $this->assertEquals([], $data);
    }

    public function testEncodeAssociativeArray()
    {
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new NvpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testEncodeIterable()
    {
        $data = new \ArrayObject();
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new NvpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testDecodeObject()
    {
        $service = new NvpQuery();
        $data = $service->decode('foo=bar&xxx=1');

        $exp['foo'] = 'bar';
        $exp['xxx'] = '1';
        $this->assertEquals($exp, $data);
    }

    public function testEncodeList()
    {
        $data['xxx'] = ['foo', 'bar'];

        $service = new NvpQuery();
        $query = $service->encode($data);

        $this->assertSame('xxx=foo&xxx=bar', $query);
    }

    public function testDecodeList()
    {
        $service = new NvpQuery();
        $data = $service->decode('xxx=foo&xxx=bar&xxx=baz');

        $exp['xxx'] = ['foo', 'bar', 'baz'];
        $this->assertEquals($exp, $data);
    }

    public function testEncodeNestedArrayFails()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Value for parameter \'foo\' cannot be encoded, a non-hash value is expected.');

        $data['foo']['bar'] = 1;

        $service = new NvpQuery();
        $service->encode($data);
    }

    public function testEncodeObjectFails()
    {
        $this->expectException(EncoderException::class);
        $this->expectExceptionMessage('Value for parameter \'foo\' cannot be encoded, a scalar value is expected.');

        $data['foo'] = new \stdClass();

        $service = new NvpQuery();
        $service->encode($data);
    }

    public function testEncodeEmptyParameter()
    {
        $data['foo'] = 'bar';
        $data['xxx'] = null;

        $service = new NvpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx', $query);
    }

    public function testDecodeEmptyParameter()
    {
        $service = new NvpQuery();
        $data = $service->decode('foo=bar&xxx');

        $exp['foo'] = 'bar';
        $exp['xxx'] = null;
        $this->assertEquals($exp, $data);
    }

    public function testEncodeBoolean()
    {
        $data['foo'] = true;
        $data['bar'] = false;

        $service = new NvpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=true&bar=false', $query);
    }
}
