<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Digua\Helper;

class HelperTest extends TestCase
{
    /**
     * @return void
     */
    public function testAddHelper(): void
    {
        $closure = fn($value) => $value;
        Helper::register('testBoolean', $closure);
        $this->assertTrue(Helper::testBoolean(true));
        $this->assertFalse(Helper::testBoolean(false));
        $this->assertEquals($closure, Helper::get('testBoolean'));

        Helper::register('testSqrt', fn($value) => (int)sqrt($value));
        $this->assertSame(3, Helper::testSqrt(9));
        $this->assertSame(4, Helper::testSqrt(16));
    }

    /**
     * @return void
     */
    public function testAddDuplicateHelper(): void
    {
        Helper::register('testInteger', fn($value) => (int)$value);
        Helper::register('testInteger', fn($value) => 5, true);
        $this->assertSame(5, Helper::testInteger(3));

        $this->expectException(ValueError::class);
        Helper::register('testInteger', fn($value) => (int)$value);
    }

    /**
     * @return void
     */
    public function testDefaultHelper(): void
    {
        $this->assertSame('test/1string-data.lg', Helper::filterFileName('+test/1; string-da&ta.lg?'));
        $this->assertIsInt(Helper::makeIntHash());
    }
}