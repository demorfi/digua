<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Exceptions\{
    Singleton as SingletonException,
    BadMethodCall as BadMethodCallException
};
use Tests\Pacifiers\StubSingleton;
use PHPUnit\Framework\TestCase;

class SingletonTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsItPossibleCreateInstance(): void
    {
        $this->assertInstanceOf(StubSingleton::class, StubSingleton::getInstance());
        $this->assertSame(StubSingleton::getInstance(), StubSingleton::getInstance());
    }

    /**
     * @return void
     */
    public function testThrowObjectUnserialize(): void
    {
        $this->expectException(SingletonException::class);
        $this->expectExceptionMessage('Object unserialize forbidden!');

        $serialized = serialize(StubSingleton::getInstance());
        unserialize($serialized);
    }

    /**
     * @return void
     */
    public function testIsItPossibleCallMethodAsStatic(): void
    {
        $this->assertSame(StubSingleton::staticMethodAsStatic(), []);
        $this->assertSame(StubSingleton::staticMethodAsStatic([1, 2], [3, 4]), [[1, 2], [3, 4]]);
    }

    /**
     * @return void
     */
    public function testThrowCallMethodAsStatic(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('method methodNever does not exist!');

        StubSingleton::staticMethodNever();
    }
}