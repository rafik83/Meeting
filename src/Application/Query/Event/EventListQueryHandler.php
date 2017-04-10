<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Application\View\Event\DayView;
use Proximum\Vimeet\Application\View\Event\EventListsView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\View\EventListView;

class EventListQueryHandler
{
    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * EventListQueryHandler constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        \DateTimeInterface $datetime
    ) {
        $this->datetime        = $datetime;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param EventListQuery $query
     *
     * @return EventListsView
     */
    public function handle(EventListQuery $query)
    {
        $events        = $this->eventRepository->getEventsWithDaysByAdmin($query->admin);
        $pastEventDate = clone $this->datetime->modify('+1 month');
        $actualEvents  = [];
        $pastEvents    = [];

        foreach ($events as $event) {
            $lastDay = $event->getLastDay();

            $eventListView = new EventListView(
                $event->getId(),
                $event->getTitle(),
                $event->getDomain(),
                $event->getLocales(),
                $event->getFallback(),
                array_map(function (Day $day) {
                    return new DayView($day->getStartTime(), $day->getEndTime());
                }, $event->getDays())
            );

            // past event
            if ($lastDay->getEndTime() >= $pastEventDate) {
                $pastEvents[] = $eventListView;
            } else {
                // actual event
                $actualEvents[] = $eventListView;
            }
        }

        return new EventListsView($actualEvents, $pastEvents);
    }
}
