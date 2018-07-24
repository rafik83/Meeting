<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Event;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Event\Event\VisioUpdatedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VisioUpdatedEventSubscriber implements EventSubscriberInterface
{
    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(JobQueueInterface $jobQueue)
    {
        $this->jobQueue = $jobQueue;
    }

    public function onVisioUpdated(VisioUpdatedEvent $event): void
    {
        $this->jobQueue->toggleParticipantVisioForEvent(
            $event->getUpdate()->event,
            $event->getUpdate()->visio
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::EVENT_VISIO_UPDATED => 'onVisioUpdated',
        ];
    }
}
