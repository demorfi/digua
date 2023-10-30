<?php declare(strict_types=1);

namespace Tests\Traits;

use Digua\Traits\Buffering;
use PHPUnit\Framework\TestCase;

class BufferingTest extends TestCase
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
            use Buffering;
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
    public function testFlushBuffering(): void
    {
        $this->assertEmpty($this->traitOutput->flushBuffering());

        $this->traitOutput->startBuffering();
        print 'test string';

        $this->assertSame($this->traitOutput->getBufferingContents(), 'test string');
        $this->assertSame($this->traitOutput->flushBuffering(), 'test string');
    }

    /**
     * @return void
     */
    public function testStopBuffering(): void
    {
        $this->assertTrue($this->traitOutput->startBuffering());
        $this->assertTrue($this->traitOutput->stopBuffering());
    }

    /**
     * @return void
     */
    public function testCleanBuffering(): void
    {
        $this->assertEmpty($this->traitOutput->flushBuffering());

        $this->traitOutput->startBuffering();
        print 'test string';

        $this->traitOutput->cleanBuffering();
        $this->assertSame($this->traitOutput->flushBuffering(), '');
    }
}