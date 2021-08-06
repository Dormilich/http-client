<?php

namespace Tests\Utility;

use Dormilich\HttpClient\Utility\PhpQuery;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Utility\PhpQuery
 */
class PhpQueryTest extends TestCase
{
    public function testEncodeEmptyArray()
    {
        $service = new PhpQuery();
        $query = $service->encode([]);

        $this->assertSame('', $query);
    }

    public function testDecodeEmptyQuery()
    {
        $service = new PhpQuery();
        $data = $service->decode('');

        $this->assertEquals([], $data);
    }

    public function testEncodeAssociativeArray()
    {
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new PhpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testEncodeIterable()
    {
        $data = new \ArrayObject();
        $data['foo'] = 'bar';
        $data['xxx'] = 1;

        $service = new PhpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo=bar&xxx=1', $query);
    }

    public function testDecodeArray()
    {
        $service = new PhpQuery();
        $data = $service->decode('foo=bar&xxx=1');

        $exp['foo'] = 'bar';
        $exp['xxx'] = '1';
        $this->assertEquals($exp, $data);
    }

    public function testEncodeList()
    {
        $data['xxx'] = ['foo', 'bar'];

        $service = new PhpQuery();
        $query = $service->encode($data);

        $this->assertSame('xxx%5B0%5D=foo&xxx%5B1%5D=bar', $query);
    }

    public function testDecodeList()
    {
        $service = new PhpQuery();
        $data = $service->decode('xxx%5B0%5D=foo&xxx%5B1%5D=bar');

        $exp['xxx'] = ['foo', 'bar'];
        $this->assertEquals($exp, $data);
    }

    public function testEncodeNestedArray()
    {
        $data['foo']['bar'] = 1;

        $service = new PhpQuery();
        $query = $service->encode($data);

        $this->assertSame('foo%5Bbar%5D=1', $query);
    }

    public function testDecodeNestedArray()
    {
        $service = new PhpQuery();
        $data = $service->decode('foo%5Bbar%5D=1');

        $exp['foo']['bar'] = 1;
        $this->assertEquals($exp, $data);
    }
}
