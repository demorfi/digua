<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Traits\Configurable;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /**
     * @var object
     */
    private object $traitConfigurable;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->traitConfigurable = new class {
            use Configurable;

            /**
             * @var array
             */
            protected static array $defaults = ['default' => 'default'];
        };
    }

    /**
     * @return void
     */
    public function testSetAndGetConfigValue(): void
    {
        $obj = new \stdClass();
        $this->traitConfigurable::setConfigValue('index', 0);
        $this->traitConfigurable::setConfigValue('name obj', $obj);
        $this->traitConfigurable::setConfigValue('enabled', false);

        $this->assertSame($this->traitConfigurable::getConfigValue('index'), 0);
        $this->assertSame($this->traitConfigurable::getConfigValue('name obj'), $obj);
        $this->assertSame($this->traitConfigurable::getConfigValue('default'), 'default');
        $this->assertSame($this->traitConfigurable::getConfigValue('enabled'), false);
        $this->assertSame($this->traitConfigurable::getConfigValue('never'), null);
    }

    /**
     * @return void
     */
    public function testHasConfigValue(): void
    {
        $this->traitConfigurable::setConfigValue('sub-index', 10);

        $this->assertTrue($this->traitConfigurable::hasConfigValue('sub-index'));
        $this->assertTrue($this->traitConfigurable::hasConfigValue('default'));
        $this->assertFalse($this->traitConfigurable::hasConfigValue('never'));
    }

    /**
     * @return void
     */
    public function testIsItPossibleReplaceDefaultConfigValue(): void
    {
        $this->assertSame($this->traitConfigurable::getConfigValue('default'), 'default');
        $this->traitConfigurable::setConfigValue('default', false);
        $this->assertSame($this->traitConfigurable::getConfigValue('default'), false);
    }
}