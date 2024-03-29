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
    public function testAppendElement(): void
    {
        $collection = ArrayCollection::make();
        $collection->append('value1');
        $collection->append('value2');
        $this->assertSame($collection->getAll(), ['value1', 'value2']);
    }

    /**
     * @return void
     */
    public function testGetFirstElement(): void
    {
        $this->assertSame('value1', ArrayCollection::make(['foo' => 'value1', 'bar' => 'value2'])->first());
        $this->assertSame(1, ArrayCollection::make([1,2,3])->first());
        $this->assertSame(null, ArrayCollection::make()->first());
    }

    /**
     * @return void
     */
    public function testGetLastElement(): void
    {
        $this->assertSame('value2', ArrayCollection::make(['foo' => 'value1', 'bar' => 'value2'])->last());
        $this->assertSame(3, ArrayCollection::make([1,2,3])->last());
        $this->assertSame(null, ArrayCollection::make()->last());
    }

    /**
     * @return void
     */
    public function testGetFirstKey(): void
    {
        $this->assertSame('foo', ArrayCollection::make(['foo' => 'value1', 'bar' => 'value2'])->firstKey());
        $this->assertSame(0, ArrayCollection::make(['foo', 'bar'])->firstKey());
    }

    /**
     * @return void
     */
    public function testGetLastKey(): void
    {
        $this->assertSame('bar', ArrayCollection::make(['foo' => 'value1', 'bar' => 'value2'])->lastKey());
        $this->assertSame(1, ArrayCollection::make(['foo', 'bar'])->lastKey());
    }

    /**
     * @return void
     */
    public function testMethodIsEmpty(): void
    {
        $collection = ArrayCollection::make(['foo' => 'value']);
        $this->assertFalse($collection->isEmpty());
        $this->assertTrue($collection->except('foo')->isEmpty());
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
    public function testMethodExcept(): void
    {
        $collection = ArrayCollection::make(['foo' => 'value', 'bar' => 'value', 'some' => 'value']);
        $this->assertSame(
            ['foo' => 'value', 'bar' => 'value', 'some' => 'value'],
            $collection->except('none')->toArray()
        );
        $this->assertSame(['foo' => 'value', 'some' => 'value'], $collection->except('bar')->toArray());
        $this->assertSame(['bar' => 'value'], $collection->except('foo', 'some')->toArray());
        $this->assertSame([], $collection->except('foo', 'some', 'bar')->toArray());
    }

    /**
     * @return void
     */
    public function testMethodCollapse(): void
    {
        $collection = ArrayCollection::make([
            'foo' => 'value',
            'bar' => [
                'foo'  => 'value',
                'bar'  => 'value',
                'some' => [1, 2, 3]
            ]
        ]);

        $this->assertSame(
            ['foo' => 'value', 'bar' => 'value', 'some' => [1, 2, 3]],
            $collection->collapse('bar')->toArray()
        );
        $this->assertSame([1, 2, 3], $collection->collapse('bar', 'some')->toArray());
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
            $collection->slice('pref2-', static fn($key, $value) => $value > 5)->toArray()
        );

        $this->assertSame(
            ['bar' => 5],
            $collection->slice('pref2-', static fn($key, $value) => $key === 'pref2-bar')->toArray()
        );

        $this->assertSame(
            ['pref1-some' => 3, 'pref2-foo' => 4],
            $collection->slice('', static fn($key, $value) => $value > 2 && $value < 5)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodFilter(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'some' => 3]);
        $this->assertSame(['bar' => 2], $collection->filter(static fn($value) => $value === 2)->toArray());
        $this->assertSame(['bar' => 2, 'some' => 3], $collection->filter(static fn($value) => $value > 1)->toArray());

        $this->assertSame(
            ['foo' => 1, 'some' => 3],
            $collection->filter(static fn($value, $key) => $key === 'foo' || $value === 3)->toArray()
        );

        $this->assertSame(
            ['bar' => 2],
            $collection->filter(static fn($key) => $key === 'bar', ARRAY_FILTER_USE_KEY)->toArray()
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
            $collection->each(static fn(&$value) => $value = $value * 2)->toArray()
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
            $collection->each(static fn(&$value) => $value = $value * 2, true)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodReplaceValue(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2]);
        $this->assertSame(
            ['foo' => 1, 'bar' => 'replaced by 2'],
            $collection->replaceValue('bar', static fn($v) => 'replaced by ' . $v)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testMethodReplaceValueRecursive(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'same' => ['bar' => 3]]);
        $this->assertSame(
            ['foo' => 1, 'bar' => 'replaced by 2', 'same' => ['bar' => 'replaced by 3']],
            $collection->replaceValue('bar', static fn($v) => 'replaced by ' . $v, true)->toArray()
        );
    }

    /**
     * @return void
     */
    public function testSearch(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'same' => ['bar' => 3]]);
        $this->assertTrue($collection->search(3)->isEmpty());
        $this->assertSame(['bar' => 2], $collection->search('2')->toArray());
        $this->assertSame('bar', $collection->search('2')->firstKey());
    }

    /**
     * @return void
     */
    public function testSearchStrict(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'same' => ['bar' => 3]]);
        $this->assertTrue($collection->search('1', true)->isEmpty());
        $this->assertSame(['foo' => 1], $collection->search(1, true)->toArray());
        $this->assertSame('foo', $collection->search(1, true)->firstKey());
    }

    /**
     * @return void
     */
    public function testSearchRecursive(): void
    {
        $collection = ArrayCollection::make(['foo' => 1, 'bar' => 2, 'same' => ['bar' => 3]]);
        $this->assertSame(['bar' => 2], $collection->search(2, true, true)->toArray());
        $this->assertSame('same', $collection->search(3, false, true)->firstKey());
    }
}