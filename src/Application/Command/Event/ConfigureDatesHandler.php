<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ConfigureDatesHandler
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * ConfigureDatesHandler constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param ConfigureDates $configureDates
     */
    public function handle(ConfigureDates $configureDates)
    {
        $configureDates->event->getConfiguration()->setDates(
            $configureDates->catalogOnlineDate,
            $configureDates->happeningsOpenDate,
            $configureDates->schedulePublishDate
        );

        $this->eventRepository->set($configureDates->event);
    }
}
