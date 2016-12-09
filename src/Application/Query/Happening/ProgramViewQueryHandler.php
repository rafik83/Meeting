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
     * @param DayRepositoryInterface $dayRepository
     * @param DayViewQueryHandler    $dayViewQueryHandler
     */
    public function __construct(DayRepositoryInterface $dayRepository, DayViewQueryHandler $dayViewQueryHandler)
    {
        $this->dayRepository       = $dayRepository;
        $this->dayViewQueryHandler = $dayViewQueryHandler;
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

        $dayViews = [];
        foreach ($eventDays as $day) {
            $dayViews[] = $this->dayViewQueryHandler->handle(
                new DayViewQuery(
                    $programViewQuery->event,
                    $day,
                    $programViewQuery->locale,
                    $programViewQuery->category
                )
            );
        }

        $categoryTitle = $programViewQuery->category !== null ? $programViewQuery->category->getTitle($programViewQuery->locale) : null;

        return new ProgramView(
            $dayViews,
            $categoryTitle
        );
    }
}
