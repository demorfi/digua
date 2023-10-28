<?php declare(strict_types=1);

namespace Tests\Components\Searching;

use Digua\Components\Searching\InArray;
use PHPUnit\Framework\TestCase;

class InArrayTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function dataSetProvider(): array
    {
        $list = range('a', 'z');
        array_unshift($list, '.');
        return ['key 1...26 value a....z' => [array_chunk($list, 1, true)]];
    }

    /**
     * @return void
     */
    public function testIsReturnGetArray(): void
    {
        $array = new InArray([1, 2, 3]);
        $this->assertSame($array->getArray(), [1, 2, 3]);
    }

    /**
     * @return void
     */
    public function testFindEmptyValue(): void
    {
        $array = new InArray([1, 2, 3]);
        $this->assertSame($array->find('any-key', null), [1, 2, 3]);
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @return void
     */
    public function testFindInArray(array $dataSet): void
    {
        $array = new InArray($dataSet);
        $this->assertSame($array->find('1', 'a'), [1 => ['1' => 'a']]);
        $this->assertSame($array->find('2', 'b'), [2 => ['2' => 'b']]);
        $this->assertSame($array->find('26', 'z'), [26 => ['26' => 'z']]);
        $this->assertSame($array->find('1', 'z'), []);
    }

    /**
     * @dataProvider dataSetProvider
     * @param array $dataSet
     * @return void
     */
    public function testFindMultipleValues(array $dataSet): void
    {
        $array = new InArray([...$dataSet, ...$dataSet]); // offset zero index (28 this 1, 29 this 2, etc...)
        $this->assertSame($array->find('1', 'a'), [1 => ['1' => 'a'], 28 => ['1' => 'a']]);
        $this->assertSame($array->find('2', 'b'), [2 => ['2' => 'b'], 29 => ['2' => 'b']]);
        $this->assertSame($array->find('26', 'z'), [26 => ['26' => 'z'], 53 => ['26' => 'z']]);
    }
}