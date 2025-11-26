<?php

namespace Corbital\ModuleManager\Services;

class ModuleEventManager
{
    /**
     * List of registered events and their handlers.
     *
     * @var array
     */
    protected $events = [];

    /**
     * Register an event handler for a specific event.
     *
     * @return void
     */
    public function listen(string $event, callable $handler, int $priority = 10)
    {
        if (! isset($this->events[$event])) {
            $this->events[$event] = [];
        }

        // Add the handler with priority
        $this->events[$event][] = [
            'handler' => $handler,
            'priority' => $priority,
        ];

        // Sort handlers by priority (higher priority first)
        usort($this->events[$event], function ($a, $b) {
            return $b['priority'] - $a['priority'];
        });
    }

    /**
     * Trigger an event and execute all registered handlers.
     *
     * @param  mixed  $data
     * @return mixed
     */
    public function trigger(string $event, $data = null)
    {
        if (! isset($this->events[$event])) {
            return $data;
        }

        $result = $data;

        try {
            foreach ($this->events[$event] as $item) {
                $handler = $item['handler'];
                $result = $handler($result);
            }
        } catch (\Exception $e) {
            app_log("Error triggering event {$event}", 'error', $e, [
                'event' => $event,

            ]);

        }

        return $result;
    }

    /**
     * Check if an event has handlers.
     */
    public function hasListeners(string $event): bool
    {
        return isset($this->events[$event]) && ! empty($this->events[$event]);
    }

    /**
     * Remove an event handler.
     */
    public function removeListener(string $event, ?callable $handler = null): bool
    {
        if (! isset($this->events[$event])) {
            return false;
        }

        if ($handler === null) {
            // Remove all handlers for this event
            unset($this->events[$event]);

            return true;
        }

        // Remove specific handler
        foreach ($this->events[$event] as $key => $item) {
            if ($item['handler'] === $handler) {
                unset($this->events[$event][$key]);
                $this->events[$event] = array_values($this->events[$event]);

                return true;
            }
        }

        return false;
    }

    /**
     * Get all registered events.
     */
    public function getEvents(): array
    {
        return array_keys($this->events);
    }

    /**
     * Filter content through a filter event.
     * Similar to trigger, but specifically named for filter operations.
     *
     * @param  mixed  $content
     * @return mixed
     */
    public function filter(string $filter, $content)
    {
        return $this->trigger($filter, $content);
    }

    /**
     * Apply an action event (without returning any value).
     * Similar to trigger, but specifically for actions that don't modify data.
     *
     * @param  mixed  $data
     * @return void
     */
    public function action(string $action, $data = null)
    {
        $this->trigger($action, $data);
    }

    /**
     * Alias for trigger() to maintain backward compatibility
     *
     * @param  mixed  $data
     * @return mixed
     */
    public function fire(string $event, $data = null)
    {
        return $this->trigger($event, $data);
    }
}
