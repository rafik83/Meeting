<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Service\Manager\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class AccessManager
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Event $event
     */
    public function openTheAgenda(Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            new \DateTime('2000-01-01 08:00:00'),
            $event->getConfiguration()->getSchedulePublishDate(),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate()
        );

        $this->eventRepository->set($event);
    }

    /**
     * @param Event $event
     */
    public function publishMeetings(Event $event)
    {
        $event->getConfiguration()->setDates(
            $event->getConfiguration()->getCatalogOnlineDate(),
            $event->getConfiguration()->getHappeningsOpenDate(),
            new \DateTime('2000-01-01 08:00:00'),
            $event->getConfiguration()->getCloseMeetingRequestDate(),
            $event->getConfiguration()->getCloseAnsweringMeetingRequestDate()
        );

        $this->eventRepository->set($event);
    }
}
