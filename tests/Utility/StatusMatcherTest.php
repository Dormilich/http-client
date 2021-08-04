<?php

namespace Tests\Utility;

use Dormilich\HttpClient\Utility\StatusMatcher;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Dormilich\HttpClient\Utility\StatusMatcher
 */
class StatusMatcherTest extends TestCase
{
    /**
     * @testWith [100, false]
     *           [200, true]
     *           [201, true]
     *           [204, true]
     *           [301, false]
     *           [304, false]
     *           [400, false]
     *           [404, false]
     *           [409, false]
     *           [500, false]
     *           [503, false]
     */
    public function testSuccessMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::success()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, false]
     *           [200, false]
     *           [201, false]
     *           [204, false]
     *           [301, false]
     *           [304, false]
     *           [400, true]
     *           [404, true]
     *           [409, true]
     *           [500, false]
     *           [503, false]
     */
    public function testClientErrorMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::clientError()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, false]
     *           [200, false]
     *           [201, false]
     *           [204, false]
     *           [301, false]
     *           [304, false]
     *           [400, false]
     *           [404, false]
     *           [409, false]
     *           [500, true]
     *           [503, true]
     */
    public function testServerErrorMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::serverError()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, false]
     *           [200, false]
     *           [201, false]
     *           [204, false]
     *           [301, false]
     *           [304, false]
     *           [400, true]
     *           [404, true]
     *           [409, true]
     *           [500, true]
     *           [503, true]
     */
    public function testErrorMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::error()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, true]
     *           [200, false]
     *           [201, false]
     *           [204, false]
     *           [301, true]
     *           [304, true]
     *           [400, true]
     *           [404, true]
     *           [409, true]
     *           [500, true]
     *           [503, true]
     */
    public function testUnexpectedMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::unexpected()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, true]
     *           [200, true]
     *           [201, true]
     *           [204, true]
     *           [301, true]
     *           [304, true]
     *           [400, true]
     *           [404, true]
     *           [409, true]
     *           [500, true]
     *           [503, true]
     */
    public function testAnyMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::any()->matches($status);

        $this->assertSame($bool, $result);
    }

    /**
     * @testWith [100, false]
     *           [200, true]
     *           [201, false]
     *           [204, false]
     *           [301, false]
     *           [304, false]
     *           [400, false]
     *           [404, false]
     *           [409, false]
     *           [500, false]
     *           [503, false]
     */
    public function testExactMatcher(int $status, bool $bool)
    {
        $result = StatusMatcher::exact(200)->matches($status);

        $this->assertSame($bool, $result);
    }
}
