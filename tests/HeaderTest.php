<?php

use Dormilich\HttpClient\Header;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Header
 */
class HeaderTest extends TestCase
{
    public function testIteration()
    {
        $header['content-type'] = 'text/plain';
        $store = new Header($header);

        $expected['Content-Type'] = ['text/plain'];
        $this->assertEquals($expected, iterator_to_array($store));
    }

    public function testGetHeader()
    {
        $header['content-type'] = 'text/plain';
        $store = new Header($header);

        $this->assertCount(1, $store);
        $this->assertTrue($store->has('content-type'));
        $this->assertTrue($store->has('Content-Type'));
        $this->assertEquals(['text/plain'], $store->get('Content-Type'));
    }

    public function testAddHeaders()
    {
        $store = new Header();
        $store->add('vary', 'Accept-Charset');
        $store->add('vary', 'Accept-Encoding');

        $this->assertCount(1, $store);
        $this->assertTrue($store->has('vary'));
        $this->assertEquals(['Accept-Charset', 'Accept-Encoding'], $store->get('Vary'));
    }

    public function testReplaceHeaders()
    {
        $store = new Header();
        $store->add('vary', 'Accept-Charset');
        $store->replace('vary', 'Accept-Encoding');

        $this->assertCount(1, $store);
        $this->assertTrue($store->has('vary'));
        $this->assertEquals(['Accept-Encoding'], $store->get('Vary'));
    }

    public function testRemoveHeaders()
    {
        $header['content-type'] = 'text/plain';
        $store = new Header($header);

        $this->assertCount(1, $store);

        $store->remove('Content-Type');

        $this->assertCount(0, $store);
    }

    public function testSetWithArrayAccess()
    {
        $store = new Header();
        $this->assertFalse(isset($store['Content-Type']));

        $store['content-type'] = 'text/plain';

        $this->assertCount(1, $store);
        $this->assertTrue(isset($store['Content-Type']));
        $this->assertEquals(['text/plain'], $store['Content-Type']);

        return $store;
    }

    /**
     * @depends testSetWithArrayAccess
     */
    public function testAddWithArrayAccess(Header $store)
    {
        $store['content-type'] = 'text/rpsl';

        $this->assertCount(1, $store);
        $this->assertEquals(['text/plain', 'text/rpsl'], $store['Content-Type']);

        return $store;
    }

    /**
     * @depends testAddWithArrayAccess
     */
    public function testRemoveWithArrayAccess(Header $store)
    {
        unset($store['content-type']);

        $this->assertCount(0, $store);
        $this->assertEquals([], $store['Content-Type']);
    }

    public function testAddHeaderWithoutNameFails()
    {
        $this->expectException(\LogicException::class);

        $store = new Header();
        $store[] = 'text/plain';
    }

    public function testAddMultipleHeaders()
    {
        $header['vary'][] = 'Accept-Charset';
        $header['vary'][] = 'Accept-Encoding';
        $store = new Header($header);

        $this->assertCount(1, $store);
    }
}
