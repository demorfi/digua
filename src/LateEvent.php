<?php declare(strict_types=1);

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
     * @param string $eventName
     * @param mixed  ...$arguments Arguments passed to the handler
     * @return int Count of notifications
     */
    public static function notify(string $eventName, mixed ...$arguments): int
    {
        $counter = 0;
        if (self::hasSubscribers($eventName)) {
            foreach (self::getSubscribers($eventName) as $handler) {
                $handler(...$arguments);
                $counter++;
            }
        }

        return $counter;
    }

    /**
     * @param string $eventName
     * @return array
     */
    public static function getSubscribers(string $eventName): array
    {
        return self::$events[$eventName] ?? [];
    }

    /**
     * @param string $eventName
     * @return bool
     */
    public static function hasSubscribers(string $eventName): bool
    {
        return isset(self::$events[$eventName]) && !empty(self::$events[$eventName]);
    }

    /**
     * @param string $eventName
     * @return int Count of removed subscribers
     */
    public static function removeSubscribers(string $eventName): int
    {
        $counter = 0;
        if (self::hasSubscribers($eventName)) {
            $counter = sizeof(self::$events[$eventName]);
            unset(self::$events[$eventName]);
        }

        return $counter;
    }

    /**
     * @return void
     */
    public static function clean(): void
    {
        self::$events = [];
    }

    /**
     * Subscribe to an event.
     *
     * @param string|array $eventName
     * @param callable     $handler
     * @return void
     */
    public static function subscribe(string|array $eventName, callable $handler): void
    {
        $events = is_string($eventName) ? [$eventName] : $eventName;
        foreach ($events as $event) {
            if (!isset(self::$events[$event])) {
                self::$events[$event] = [];
            }

            self::$events[$event][] = $handler;
        }
    }
}