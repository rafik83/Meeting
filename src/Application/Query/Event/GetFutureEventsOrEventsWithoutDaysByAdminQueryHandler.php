<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Event;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class GetFutureEventsOrEventsWithoutDaysByAdminQueryHandler
{
    /** @var EventRepositoryInterface $eventRepository */
    private $eventRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(EventRepositoryInterface $eventRepository, \DateTimeInterface $datetime)
    {
        $this->eventRepository = $eventRepository;
        $this->datetime = $datetime;
    }

    public function handle(GetFutureEventsOrEventsWithoutDaysByAdminQuery $query): iterable
    {
        return $this->eventRepository->findFutureEventsOrEventsWithoutDaysByAdmin(
            $query->admin,
            $query->excludedEvent,
            $this->datetime
        );
    }
}
