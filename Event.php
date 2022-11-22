<?php

namespace Digua;

class Event
{
    /**
     * @var array
     */
    private static array $events = [];

    /**
     * Register event.
     *
     * @param string|array $eventName
     * @param mixed        ...$arguments Arguments passed to the handler
     * @return void
     */
    public static function register(string|array $eventName, mixed ...$arguments): void
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

    /**
     * Unsubscribe from an event.
     *
     * @param string            $handlerId
     * @param string|array|null $eventName
     * @return void
     */
    public static function unsubscribe(string $handlerId, string|array $eventName = null): void
    {
        $events = is_string($eventName) ? [$eventName] : (empty($eventName) ? [] : $eventName);
        foreach (self::$events as $name => $event) {
            if (isset($event[$handlerId]) && (empty($events) || in_array($name, $events))) {
                unset(self::$events[$name][$handlerId]);
            }
        }
    }
}