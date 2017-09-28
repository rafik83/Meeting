<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\MeetingRequest;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\CreateRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SMSNotifierSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::MEETING_REQUEST_CREATED => 'onMeetingRequestCreated'
        ];
    }

    public function onMeetingRequestCreated(CreateRequestEvent $event)
    {
        
    }
}
