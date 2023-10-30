<?php declare(strict_types=1);

namespace Tests;

use Digua\Helper;
use Digua\Exceptions\BadMethodCall as BadMethodCallException;
use PHPUnit\Framework\TestCase;
use ValueError;

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
        $this->assertSame($closure, Helper::get('testBoolean'));

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
    public function testHelperFilterFileName(): void
    {
        $this->assertSame('test/1string-data.lg', Helper::filterFileName('+test/1; string-da&ta.lg?'));
    }

    /**
     * @return void
     */
    public function testHelperMakeIntHash(): void
    {
        $this->assertIsInt(Helper::makeIntHash());
    }

    /**
     * @return void
     */
    public function testThrowInvalidHelperMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('helper method never does not exist!');
        Helper::never();
    }
}