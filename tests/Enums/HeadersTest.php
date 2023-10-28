<?php declare(strict_types=1);

namespace Tests\Enums;

use Digua\Enums\Headers;
use PHPUnit\Framework\TestCase;

class HeadersTest extends TestCase
{
    /**
     * @return array[]
     */
    protected function dataSetProvider(): array
    {
        $list = [];
        foreach (Headers::cases() as $case) {
            $list[$case->name] = $case;
        }
        return array_combine(array_keys($list), array_chunk($list, 1));
    }

    /**
     * @dataProvider dataSetProvider
     * @param Headers $enum
     * @return void
     */
    public function testGetText(Headers $enum): void
    {
        $this->assertNotEmpty($enum->getText());
        $this->assertIsString($enum->getText());
    }
}