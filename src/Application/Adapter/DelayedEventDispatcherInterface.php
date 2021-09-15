<?php

namespace Proximum\Vimeet\Application\Adapter;

use Symfony\Component\EventDispatcher\Event;

interface DelayedEventDispatcherInterface
{
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     * @param Event  $event     The event to pass to the event handlers/listeners
     *                          If not supplied, an empty Event instance is created.
     *
     * @return Event
     */
    public function dispatch($eventName, Event $event = null);
}
