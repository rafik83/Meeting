<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Event;

interface ApplicationEventDispatcherInterface
{
    /**
     * @param string           $eventName
     * @param ApplicationEvent $event
     *
     * @return mixed
     */
    public function dispatch($eventName, ApplicationEvent $event);
}
