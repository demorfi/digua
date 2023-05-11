<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\ArrayCollection;
use Digua\Interfaces\NamedCollection;
use Digua\Traits\Data;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use IteratorAggregate;
use ArrayAccess;
use JsonSerializable;
use Countable;


class ArrayCollectionTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function collectionProvider(): array
    {
        return [
            [['key' => 'value', 'foo' => 'bar']]
        ];
    }

    /**
     * @return void
     */
    public function testInstanceOfInterfaces(): void
    {
        $collection = new ArrayCollection;
        $this->assertInstanceOf(NamedCollection::class, $collection);
        $this->assertInstanceOf(Countable::class, $collection);
        $this->assertInstanceOf(ArrayAccess::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertInstanceOf(JsonSerializable::class, $collection);
    }

    /**
     * @return void
     */
    public function testUsesDataTrait(): void
    {
        $traits = (new ReflectionClass(new ArrayCollection))->getTraitNames();
        $this->assertCount(1, $traits);
        $this->assertSame(Data::class, $traits[0]);
    }

    /**
     * @dataProvider collectionProvider
     * @param array $data
     * @return void
     */
    public function testCreateCollection(array $data): void
    {
        $this->assertSame($data, (new ArrayCollection($data))->getAll());
    }

    /**
     * @dataProvider collectionProvider
     * @param array $data
     * @return void
     */
    public function testStaticMakeCollection(array $data): void
    {
        $this->assertSame($data, ArrayCollection::make($data)->getAll());
    }

    /**
     * @return void
     */
    public function testMethodOnly(): void
    {
        $collection = ArrayCollection::make(['foo' => 'value', 'bar' => 'value', 'some' => 'value']);
        $this->assertSame(['bar' => 'value'], $collection->only('bar')->toArray());
        $this->assertSame(['some' => 'value'], $collection->only('some')->toArray());
        $this->assertSame(['some' => 'value', 'foo' => 'value'], $collection->only('some', 'foo')->toArray());
    }

    /**
     * @return void
     */
    public function testMethodSlice(): void
    {
        $collection = ArrayCollection::make([
            'pref1-foo'  => 1,
            'pref1-bar'  => 2,
            'pref1-some' => 3,
            'pref2-foo'  => 4,
            'pref2-bar'  => 5,
            'pref2-some' => 6,
        ]);

        $this->assertSame(['foo' => 1, 'bar' => 2, 'some' => 3], $collection->slice('pref1-')->toArray());
        $this->assertSame(['foo' => 4, 'bar' => 5, 'some' => 6], $collection->slice('pref2-')->toArray());

        $this->assertSame(
            ['some' => 6],
            $collection->slice('pref2-', fn($key, $value) => $value > 5)->toArray()
        );

        $this->assertSame(
            ['bar' => 5],
            $collection->slice('pref2-', fn($key, $value) => $key === 'pref2-bar')->toArray()
        );

        $this->assertSame(
            ['pref1-some' => 3, 'pref2-foo' => 4],
            $collection->slice('', fn($key, $value) => $value > 2 && $value < 5)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodFilter(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'some' => 3]);
        $this->assertSame(['bar' => 2], $collection->filter(fn($value) => $value === 2)->toArray());
        $this->assertSame(['bar' => 2, 'some' => 3], $collection->filter(fn($value) => $value > 1)->toArray());

        $this->assertSame(
            ['foo' => 1, 'some' => 3],
            $collection->filter(fn($value, $key) => $key === 'foo' || $value === 3)->toArray()
        );

        $this->assertSame(
            ['bar' => 2],
            $collection->filter(fn($key) => $key === 'bar', ARRAY_FILTER_USE_KEY)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodMerge(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2]);
        $this->assertSame(
            ['foo' => 1, 'bar' => 2, 'some' => 3],
            $collection->merge(['some' => 3])->toArray()
        );

        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2]);
        $this->assertSame(
            ['foo' => 5, 'bar' => 2, 'some' => 3],
            $collection->merge(['some' => 3, 'foo' => 5])->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMergeRecursive(): void
    {
        $collection = ArrayCollection::make(['foo' => [1, 1, 'sub' => 1], 'bar' => [2, 2]]);
        $this->assertSame(
            ['foo' => [1, 1, 'sub' => [1, 2], 3], 'bar' => [2, 2], 'some' => 3],
            $collection->merge(['some' => 3, 'foo' => [3, 'sub' => 2]], true)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodEach(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2]);
        $this->assertSame(
            ['foo' => 2, 'bar' => 4],
            $collection->each(fn(&$value) => $value = $value * 2)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testEachRecursive(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'same' => [1, 2]]);
        $this->assertSame(
            ['foo' => 2, 'bar' => 4, 'same' => [2, 4]],
            $collection->each(fn(&$value) => $value = $value * 2, true)->toArray()
        );
    }
}