<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Traits\Output;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    /**
     * @var object
     */
    private object $traitOutput;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        ob_end_clean();
        $this->traitOutput = new class {
            use Output;
        };
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ob_start();
    }

    /**
     * @return void
     */
    public function testFlushBuffer(): void
    {
        $this->assertFalse($this->traitOutput->flushBuffer());

        $this->traitOutput->startBuffer();
        print 'test string';

        $this->assertSame($this->traitOutput->flushBuffer(), 'test string');
    }

    /**
     * @return void
     */
    public function testCleanBuffer(): void
    {
        $this->assertFalse($this->traitOutput->flushBuffer());

        $this->traitOutput->startBuffer();
        print 'test string';

        $this->traitOutput->cleanBuffer();
        $this->assertSame($this->traitOutput->flushBuffer(), '');
    }
}