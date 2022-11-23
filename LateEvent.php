<?php declare(strict_types = 1);

namespace Digua;

class LateEvent
{
    /**
     * @var array
     */
    private static array $events = [];

    /**
     * Notify event.
     *
     * @param string|array $eventName
     * @param mixed        ...$arguments Arguments passed to the handler
     * @return void
     */
    public static function notify(string|array $eventName, mixed ...$arguments): void
    {
        $events = is_string($eventName) ? [$eventName] : $eventName;
        foreach ($events as $event) {
            if (isset(self::$events[$event])) {
                foreach (self::$events[$event] as $handler) {
                    $handler(...$arguments);
                }
            }
        }
    }

    /**
     * Subscribe to an event.
     *
     * @param string       $handlerId
     * @param string|array $eventName
     * @param callable     $handler
     * @return void
     */
    public static function subscribe(string $handlerId, string|array $eventName, callable $handler): void
    {
        $events = is_string($eventName) ? [$eventName] : $eventName;
        foreach ($events as $event) {
            self::$events[$event][$handlerId] ??= $handler;
        }
    }
}