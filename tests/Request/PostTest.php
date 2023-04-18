<?php declare(strict_types=1);

namespace Request;

use PHPUnit\Framework\TestCase;
use Digua\Request\{Post, FilteredInput};
use Digua\Interfaces\Request\FilteredCollection as FilteredCollectionInterface;

class PostTest extends TestCase
{
    /**
     * @var Post
     */
    private Post $post;

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
        $this->post = new Post($input);
    }

    /**
     * @return void
     */
    public function testInstanceOfFilteredCollection(): void
    {
        $this->assertInstanceOf(FilteredCollectionInterface::class, $this->post);
    }

    /**
     * @return void
     */
    public function testIsItPossibleToGetDataContent(): void
    {
        $this->data = ['var' => 'value', 'var2' => 'value2'];
        $this->post->shake();
        $this->assertEquals($this->data, $this->post->getAll());
    }
}