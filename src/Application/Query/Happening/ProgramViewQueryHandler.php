<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;

class ProgramViewQueryHandler
{
    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var DayViewQueryHandler
     */
    private $dayViewQueryHandler;

    /**
     * @var HappeningParticipationQueryHandler
     */
    private $happeningParticipationQueryHandler;

    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @param DayRepositoryInterface             $dayRepository
     * @param DayViewQueryHandler                $dayViewQueryHandler
     * @param HappeningParticipationQueryHandler $happeningParticipationQueryHandler
     * @param MassRepositoryInterface            $massRepository
     */
    public function __construct(
        DayRepositoryInterface $dayRepository,
        DayViewQueryHandler $dayViewQueryHandler,
        HappeningParticipationQueryHandler $happeningParticipationQueryHandler,
        MassRepositoryInterface $massRepository
    ) {
        $this->dayRepository                      = $dayRepository;
        $this->dayViewQueryHandler                = $dayViewQueryHandler;
        $this->happeningParticipationQueryHandler = $happeningParticipationQueryHandler;
        $this->massRepository                     = $massRepository;
    }

    /**
     * @param ProgramViewQuery $programViewQuery
     *
     * @return ProgramView
     * @throws MissingEventDayConfigurationException
     */
    public function handle(ProgramViewQuery $programViewQuery)
    {
        $eventDays = $this->dayRepository->findByEvent($programViewQuery->event);

        if (empty($eventDays)) {
            throw new MissingEventDayConfigurationException();
        }

        $masses = [];

        if ($programViewQuery->category === null) {
            $masses = $this->massRepository->findByEvent($programViewQuery->event, $programViewQuery->locale);
        }

        $dayViews = [];
        foreach ($eventDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $programViewQuery->event,
                    $day,
                    $programViewQuery->locale,
                    $programViewQuery->category,
                    $masses
                )
            );
        }

        $categoryTitle = $programViewQuery->category !== null
            ? $programViewQuery->category->getTitle($programViewQuery->locale)
            : null;

        $programView = new ProgramView(
            $dayViews,
            $categoryTitle
        );

        $this->happeningParticipationQueryHandler->handle(
            new HappeningParticipationQuery(
                $programView,
                $programViewQuery->sheet,
                $programViewQuery->user
            )
        );

        return $programView;
    }
}
