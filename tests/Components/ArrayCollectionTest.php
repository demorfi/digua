<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\ArrayCollection;
use Digua\Traits\Data;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

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
    public function testUsesDataTrait(): void
    {
        $traits = (new ReflectionClass(new ArrayCollection([])))->getTraitNames();
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
}