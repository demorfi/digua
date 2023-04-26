<?php declare(strict_types=1);

namespace Tests\Request;

use Digua\Request\{Cookies, FilteredInput};
use Digua\Interfaces\Request\FilteredCollection as FilteredCollectionInterface;
use PHPUnit\Framework\TestCase;

class CookiesTest extends TestCase
{
    /**
     * @var Cookies
     */
    private Cookies $cookies;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $input = $this->getMockBuilder(FilteredInput::class)
            ->onlyMethods(['filteredList'])
            ->getMock();

        $input->method('filteredList')->willReturnCallback(fn() => $this->data);
        $this->cookies = new Cookies($input);
    }

    /**
     * @return void
     */
    public function testInstanceOfFilteredCollection(): void
    {
        $this->assertInstanceOf(FilteredCollectionInterface::class, $this->cookies);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetDataContent(): void
    {
        $this->data = ['var' => 'value', 'var2' => 'value2'];
        $this->cookies->shake();
        $this->assertSame($this->data, $this->cookies->getAll());
    }
}