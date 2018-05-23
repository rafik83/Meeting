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

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function handle(GetFutureEventsOrEventsWithoutDaysByAdminQuery $query): iterable
    {
        return $this->eventRepository->findFutureEventsOrEventsWithoutDaysByAdmin(
            $query->admin,
            $query->excludedEvent
        );
    }
}
