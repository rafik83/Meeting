<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Event\KeyDatesUpdatedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureDatesHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param EventRepositoryInterface        $eventRepository
     * @param DelayedEventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        DelayedEventDispatcherInterface $eventDispatcher
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ConfigureDates $configureDates
     */
    public function handle(ConfigureDates $configureDates): void
    {
        $configureDates->event->getConfiguration()->setDates(
            $configureDates->catalogOnlineDate,
            $configureDates->happeningsOpenDate,
            $configureDates->schedulePublishDate,
            $configureDates->closeMeetingRequestDate,
            $configureDates->closeAnsweringMeetingRequestDate,
            $configureDates->smsActivationDate,
            $configureDates->agendaOnlineDate,
            $configureDates->registrationOpenDate,
            $configureDates->registrationCloseDate,
            $configureDates->enableBadgeForParticipantDate,
            $configureDates->enableVisioTestMenuButtonDate
        );

        $this->eventRepository->set($configureDates->event);

        $this->eventDispatcher->dispatch(
            Events::EVENT_KEY_DATES_UPDATED,
            new KeyDatesUpdatedEvent($configureDates->event)
        );
    }
}
