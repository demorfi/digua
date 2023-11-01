<?php declare(strict_types=1);

namespace Tests\Components;

use Digua\Components\Event;
use Digua\Exceptions\BadMethodCall as BadMethodCallException;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    /**
     * @return void
     */
    public function testMakeEvent(): void
    {
        $event = Event::make(['param' => 'value', 'param2' => 'value2']);
        $this->assertInstanceOf(Event::class, $event);
    }

    /**
     * @return void
     */
    public function testSetAndGetParams(): void
    {
        $event         = new Event([1 => 'value', 'param1' => 'value1', 'param2' => 'value2']);
        $event->{3}    = 'value3';
        $event->param4 = 'value4';
        $this->assertSame($event->{3}, 'value3');
        $this->assertSame($event->param4, 'value4');
    }

    /**
     * @return void
     */
    public function testInvokeHandlers(): void
    {
        $event = new Event(['key' => 1]);
        $event->addHandler(fn($event) => $event->key += 1);
        $event->addHandler(function ($event1, $previous, $arg) use ($event) {
            $this->assertSame($event, $event1);
            $event1->key += 2;
            return $previous + $arg;
        });

        $this->assertSame(12, $event(10));
        $this->assertSame(4, $event->key);
    }

    /**
     * @return void
     */
    public function testCallClosureParams(): void
    {
        $event = new Event(['called' => fn($arg1, $arg2) => $arg1 + $arg2]);
        $this->assertSame(3, $event->called(2, 1));
    }

    /**
     * @return void
     */
    public function testThrowCallNotClosureParams(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Closure (called) does not exist!');
        $event = new Event(['called' => 'string']);
        $event->called();
    }
}