<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Delay event processing after response is sent.
 * @see docs/Infrastructure/DelayedEventDispatcher.md
 */
class DelayedEventDispatcher implements EventDispatcherInterface, DelayedEventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $ready;

    /**
     * DelayedEventDispatcher constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param bool                     $ready
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $ready = false)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->ready           = $ready;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($eventName, Event $event = null)
    {
        return $this->ready ? $this->eventDispatcher->dispatch($eventName, $event) : $this->delay($eventName, $event);
    }

    /**
     * @param string     $eventName
     * @param Event|null $event
     *
     * @return Event
     */
    private function delay($eventName, Event $event = null)
    {
        $this->queue[] = [$eventName, $event];

        return $event;
    }

    /**
     * Process queue
     */
    public function process()
    {
        $this->ready = true;

        while ($delayed = array_shift($this->queue)) {
            $this->dispatch($delayed[0], $delayed[1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->eventDispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->addSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function removeListener($eventName, $listener)
    {
        return $this->eventDispatcher->removeListener($eventName, $listener);
    }

    /**
     * {@inheritdoc}
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        return $this->eventDispatcher->removeSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function getListeners($eventName = null)
    {
        return $this->eventDispatcher->getListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function hasListeners($eventName = null)
    {
        return $this->eventDispatcher->hasListeners($eventName);
    }

    /**
     * {@inheritdoc}
     */
    public function getListenerPriority($eventName, $listener)
    {
        return $this->eventDispatcher->getListenerPriority($eventName, $listener);
    }
}
