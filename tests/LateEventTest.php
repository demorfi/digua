<?php declare(strict_types=1);

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
        LateEvent::subscribe('eventName', fn() => null);
        $this->assertTrue(LateEvent::hasSubscribers('eventName'));

        $countSubscribers = sizeof(LateEvent::getSubscribers('eventName'));
        $this->assertEquals(1, $countSubscribers);

        LateEvent::clean();
        $this->assertFalse(LateEvent::hasSubscribers('eventName'));
        $countSubscribers = sizeof(LateEvent::getSubscribers('eventName'));
        $this->assertEquals(0, $countSubscribers);

        LateEvent::subscribe('eventName', fn() => null);
        $this->assertTrue(LateEvent::hasSubscribers('eventName'));

        $this->assertEquals(1, LateEvent::removeSubscribers('eventName'));
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
        $handler = (function (...$receivedArguments) use (&$success, $arguments) {
            $this->assertEqualsCanonicalizing($arguments, $receivedArguments);
            $success++;
        })(...);

        LateEvent::subscribe($eventName, $handler);

        if (is_array($eventName)) {
            $uniqueEvents = array_count_values($eventName);
            foreach ($uniqueEvents as $event => $count) {
                $this->assertTrue(LateEvent::hasSubscribers($event));
                $this->assertEquals(array_fill(0, $count, $handler), LateEvent::getSubscribers($event));

                $countNotifications = LateEvent::notify($event, ...$arguments);
                $this->assertEquals($count, $countNotifications);
            }
            $this->assertEquals(sizeof($eventName), $success);
        } else {
            $this->assertTrue(LateEvent::hasSubscribers($eventName));
            $this->assertEquals([$handler], LateEvent::getSubscribers($eventName));

            $countNotifications = LateEvent::notify($eventName, ...$arguments);
            $this->assertEquals(1, $success);
            $this->assertEquals(1, $countNotifications);
        }
    }
}