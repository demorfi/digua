<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Components\{ArrayCollection, Types};
use Digua\Traits\Data;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var object
     */
    private object $traitData;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->traitData = new class {
            use Data;
        };
    }

    /**
     * @return void
     */
    public function testSetAndGetValue(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set(1, 'index');
        $this->traitData->property = 'value';

        $this->assertSame($this->traitData->key, 'value');
        $this->assertSame($this->traitData->property, 'value');
        $this->assertSame($this->traitData->get('property'), 'value');
        $this->assertSame($this->traitData->get('key'), 'value');
        $this->assertSame($this->traitData->get(1), 'index');
        $this->assertSame($this->traitData->get('never'), null);
        $this->assertSame($this->traitData->get('never', 'default'), 'default');
    }

    /**
     * @return void
     */
    public function testHasValue(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set(1, 'index');

        $this->assertTrue($this->traitData->has('key'));
        $this->assertTrue($this->traitData->has(1));
        $this->assertTrue(isset($this->traitData->key));
    }

    /**
     * @return void
     */
    public function testIsItPossibleUnsetValue(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('property', 'value');

        $this->assertTrue($this->traitData->has('key'));
        $this->traitData->remove('key');
        $this->assertFalse($this->traitData->has('key'));

        $this->assertTrue($this->traitData->has('property'));
        unset($this->traitData->property);
        $this->assertFalse($this->traitData->has('property'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetValueByType(): void
    {
        $this->traitData->set('key', 'value');

        $this->assertInstanceOf(Types::class, $this->traitData->getTypeValue('key'));
        $this->assertSame($this->traitData->getTypeValue('key')->getValue(), 'value');
        $this->assertSame($this->traitData->getTypeValue('never', 'default')->getValue(), 'default');
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetValueByFixedType(): void
    {
        $this->traitData->set('key', '1');

        $this->assertSame($this->traitData->getFixedTypeValue('key', 'int'), 1);
        $this->assertSame($this->traitData->getFixedTypeValue('never', 'bool', 'false'), false);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetCollectionValues(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');

        $this->assertInstanceOf(ArrayCollection::class, $this->traitData->collection());
        $this->assertSame($this->traitData->collection()->getAll(), ['key' => 'value', 'key2' => 'value2']);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetAllKeySets(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->getAll(), ['key' => 'value', 'key2' => 'value2']);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetAllKeys(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->getKeys(), ['key', 'key2']);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetAllValues(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->getValues(), ['value', 'value2']);
    }

    /**
     * @return void
     */
    public function testIsItPossibleGetSize(): void
    {
        $this->traitData->set('key', 'value');
        $this->assertSame($this->traitData->size(), 1);

        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->size(), 2);
    }

    /**
     * @return void
     */
    public function testIsItPossibleRemoveKeySets(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->size(), 2);

        $this->traitData->flush();
        $this->assertSame($this->traitData->size(), 0);
        $this->assertSame($this->traitData->get('key'), null);
    }

    /**
     * @return void
     */
    public function testIsItPossibleOverwriteKeySets(): void
    {
        $this->traitData->set('key', 'value');
        $this->traitData->set('key2', 'value2');
        $this->assertSame($this->traitData->getAll(), ['key' => 'value', 'key2' => 'value2']);

        $this->traitData->overwrite(['key3' => 'value3', 'key4' => 'value4', 'key5' => 'value5']);
        $this->assertSame($this->traitData->getAll(), ['key3' => 'value3', 'key4' => 'value4', 'key5' => 'value5']);
        $this->assertSame($this->traitData->size(), 3);
    }
}