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
use Symfony\Component\EventDispatcher\Event;

class ApplicationWrappedEvent extends Event
{
    /**
     * @var ApplicationEvent
     */
    private $applicationEvent;

    /**
     * @param ApplicationEvent $applicationEvent
     */
    public function __construct(ApplicationEvent $applicationEvent)
    {
        $this->applicationEvent = $applicationEvent;
    }

    /**
     * @return ApplicationEvent
     */
    public function getApplicationEvent()
    {
        return $this->applicationEvent;
    }
}
