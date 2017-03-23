<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\View\Agenda\AgendaSpotView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class AgendaSpotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DaySpotViewQueryHandler
     */
    private $daySpotViewQueryHandler;

    /**
     * AgendaSpotViewQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param DayRepositoryInterface         $dayRepository
     * @param DaySpotViewQueryHandler        $daySpotViewQueryHandler
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        DayRepositoryInterface $dayRepository,
        DaySpotViewQueryHandler $daySpotViewQueryHandler
    ) {
        $this->meetingSlotRepository   = $meetingSlotRepository;
        $this->dayRepository           = $dayRepository;
        $this->daySpotViewQueryHandler = $daySpotViewQueryHandler;
    }

    /**
     * @param AgendaSpotViewQuery $query
     *
     * @return AgendaSpotView
     */
    public function handle(AgendaSpotViewQuery $query)
    {
        $eventDays = $this->dayRepository->findByEvent($query->event);

        $dayViews = [];

        foreach ($eventDays as $dayNumber => $day) {
            $dayViews[] = $this->daySpotViewQueryHandler->handle(
                new DaySpotViewQuery($day, $dayNumber, $query->event, $query->spot)
            );
        }

        return new AgendaSpotView(
            $query->spot->getId(),
            $query->spot->getReference(),
            $dayViews
        );
    }
}
