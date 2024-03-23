<?php declare(strict_types=1);

namespace Tests\Request;

use Digua\Components\FileUpload;
use Digua\Request\{Files, FilteredInput};
use Digua\Interfaces\Request\FilteredCollection as FilteredCollectionInterface;
use PHPUnit\Framework\TestCase;

class FilesTest extends TestCase
{
    /**
     * @var Files
     */
    private Files $files;

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
        $this->files = new Files($input);
    }

    /**
     * @return void
     */
    public function testInstanceOfFilteredCollection(): void
    {
        $this->assertInstanceOf(FilteredCollectionInterface::class, $this->files);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetDataContent(): void
    {
        $this->data = [['name' => 'test'], ['name' => 'test2']];
        $this->files->shake();
        $this->assertEquals([new FileUpload(['name' => 'test']), new FileUpload(['name' => 'test2'])], $this->files->getAll());
    }
}