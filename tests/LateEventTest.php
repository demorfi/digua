<?php declare(strict_types=1);

namespace Tests;

use Digua\LateEvent;
use PHPUnit\Framework\TestCase;

class LateEventTest extends TestCase
{
    /**
     * @return void
     */
    public function testCleaningSubscribers(): void
    {
        LateEvent::clean();
        $this->assertFalse(LateEvent::hasSubscribers('eventName'));
        LateEvent::subscribe('eventName', static fn() => null);
        $this->assertTrue(LateEvent::hasSubscribers('eventName'));

        $this->assertSame(1, sizeof(LateEvent::getSubscribers('eventName')));

        LateEvent::clean();
        $this->assertFalse(LateEvent::hasSubscribers('eventName'));
        $this->assertSame(0, sizeof(LateEvent::getSubscribers('eventName')));

        LateEvent::subscribe('eventName', static fn() => null);
        $this->assertTrue(LateEvent::hasSubscribers('eventName'));
        $this->assertSame(1, LateEvent::removeSubscribers('eventName'));
        $this->assertFalse(LateEvent::hasSubscribers('eventName'));
    }

    /**
     * @param string|array $eventName
     * @param mixed  ...$arguments
     * @testWith
     * ["eventName", true]
     * ["eventName", "oneArgument", "twoArgument"]
     * [["eventName", "eventName", "eventName"], 1, 2, 3, 4, 5]
     * [["eventName", "eventName2", "eventName3"], true, false]
     * [["eventName", "eventName", "eventName2", "eventName2"], "", null]
     * @return void
     */
    public function testIsItPossibleToNotifyEvent(string|array $eventName, mixed ...$arguments): void
    {
        LateEvent::clean();
        $success = 0;
        $handler = function (...$receivedArguments) use (&$success, $arguments) {
            $this->assertEqualsCanonicalizing($arguments, $receivedArguments);
            $success++;
        };

        LateEvent::subscribe($eventName, $handler);

        if (is_array($eventName)) {
            $uniqueEvents = array_count_values($eventName);
            foreach ($uniqueEvents as $event => $count) {
                $this->assertTrue(LateEvent::hasSubscribers($event));
                $this->assertSame(array_fill(0, $count, $handler), LateEvent::getSubscribers($event));

                $countNotifications = LateEvent::notify($event, ...$arguments);
                $this->assertSame($count, $countNotifications);
            }
            $this->assertSame(sizeof($eventName), $success);
        } else {
            $this->assertTrue(LateEvent::hasSubscribers($eventName));
            $this->assertSame([$handler], LateEvent::getSubscribers($eventName));

            $countNotifications = LateEvent::notify($eventName, ...$arguments);
            $this->assertSame(1, $success);
            $this->assertSame(1, $countNotifications);
        }
    }
}