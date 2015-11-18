<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\Event;

use Proximum\Vimeet\Application\Event\ApplicationEvent;
use Proximum\Vimeet\Application\Event\ApplicationEventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher implements ApplicationEventDispatcherInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string           $eventName
     * @param ApplicationEvent $applicationEvent
     *
     * @return Event
     */
    public function dispatch($eventName, ApplicationEvent $applicationEvent)
    {
        return $this->eventDispatcher->dispatch($eventName, new ApplicationWrappedEvent($applicationEvent));
    }
}
