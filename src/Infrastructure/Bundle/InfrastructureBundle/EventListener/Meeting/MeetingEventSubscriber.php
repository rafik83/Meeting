<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MeetingEventSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_CREATED => 'onMeetingCreated',
        ];
    }

    public function onMeetingCreated(MeetingCreatedEvent $event)
    {
        if (!$event->getMeeting()->isCreatedByParticipants()) {
            return;
        }


    }
}
