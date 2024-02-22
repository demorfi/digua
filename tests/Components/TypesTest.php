<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\{Types, ArrayCollection};
use PHPUnit\Framework\TestCase;

class TypesTest extends TestCase
{
    const TYPES = ['bool', 'boolean', 'int', 'integer', 'double', 'float', 'string', 'array', 'object', 'null', 'collection'];

    /**
     * @return array[]
     */
    protected function dataTypesProvider(): array
    {
        return [
            'null'       => ['null', 'null', null],
            'bool'       => ['bool', 'boolean', true, false],
            'int'        => ['int', 'integer', PHP_INT_MIN, PHP_INT_MAX],
            'float'      => ['float', 'float', PHP_FLOAT_MIN, PHP_FLOAT_MAX],
            'string'     => ['string', 'string', 'test string'],
            'array'      => ['array', 'array', []],
            'object'     => ['object', 'object', $this],
            'collection' => ['object', 'collection', new ArrayCollection]
        ];
    }

    /**
     * @dataProvider dataTypesProvider
     * @param string $shortName
     * @param string $longName
     * @param mixed  ...$values
     * @return void
     */
    public function testNameOfValue(string $shortName, string $longName, mixed ...$values): void
    {
        foreach ($values as $value) {
            $this->assertSame($shortName, Types::value($value)->getNameShort());
            $this->assertSame($longName, Types::value($value)->getNameLong());
        }
    }

    /**
     * @dataProvider dataTypesProvider
     * @param string $shortName
     * @param string $longName
     * @param mixed  ...$values
     * @return void
     */
    public function testIsItPossibleToReturnValue(string $shortName, string $longName, mixed ...$values): void
    {
        foreach ($values as $value) {
            $this->assertSame($value, Types::value($value)->getValue());
        }
    }

    /**
     * @dataProvider dataTypesProvider
     * @param string $shortName
     * @param string $longName
     * @return void
     */
    public function testNameOfType(string $shortName, string $longName): void
    {
        $this->assertSame($shortName, Types::type($shortName)->getNameShort());
        $this->assertSame($shortName, Types::type($longName)->getNameShort());
        $this->assertSame($longName, Types::type($longName)->getNameLong());
    }

    /**
     * @dataProvider dataTypesProvider
     * @param string $shortName
     * @param string $longName
     * @param mixed  ...$values
     * @return void
     */
    public function testIsType(string $shortName, string $longName, mixed ...$values): void
    {
        foreach ($values as $value) {
            $this->assertTrue(Types::value($value)->is($shortName));
            $this->assertTrue(Types::value($value)->is($longName));
        }
    }

    /**
     * @return void
     */
    public function testIsObjectTypeOfClassName(): void
    {
        $this->assertTrue(Types::value($this)->is($this::class));
    }

    /**
     * @return void
     */
    public function testIsNullType(): void
    {
        $this->assertTrue(Types::value(null)->isNull());
        $this->assertFalse(Types::value(false)->isNull());
    }

    /**
     * @dataProvider dataTypesProvider
     * @param string $shortName
     * @param string $longName
     * @param mixed  ...$values
     * @return void
     */
    public function testConvertType(string $shortName, string $longName, mixed ...$values): void
    {
        $exceptions = ['array' => ['string'], 'object' => ['int', 'float', 'string']];
        foreach ($values as $value) {
            $tValue = Types::value($value);
            foreach (self::TYPES as $type) {
                $tType     = Types::type($type);
                $exception = $exceptions[$tValue->getNameShort()] ?? false;
                if (is_array($exception) && in_array($tType->getNameShort(), $exception)) {
                    continue;
                }

                $this->assertSame($tType->getNameShort(), $tValue->to($type)->getNameShort());
            }
        }
    }

    /**
     * @return void
     */
    public function testConvertTypeStringToBoolType(): void
    {
        $this->assertTrue(Types::value('true')->to('bool')->getValue());
        $this->assertTrue(Types::value('1')->to('bool')->getValue());
        $this->assertFalse(Types::value('false')->to('boolean')->getValue());
        $this->assertFalse(Types::value('0')->to('bool')->getValue());
    }
}