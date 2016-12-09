<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\View\Happening\HappeningListView;
use Proximum\Vimeet\Application\View\Happening\HappeningView;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningViewQueryHandler
{
    /**
     * @var HappeningRepositoryInterface
     */
    private $happeningRepository;

    /**
     * @var DayRepositoryInterface
     */
    private $dayRepository;

    /**
     * @var SpeakerViewQueryHandler
     */
    private $speakerViewQueryHandler;

    /**
     * @var CategoryViewQueryHandler
     */
    private $categoryViewQueryHandler;

    /**
     * HappeningViewQueryHandler constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param DayRepositoryInterface       $dayRepository
     * @param SpeakerViewQueryHandler      $speakerViewQueryHandler
     * @param CategoryViewQueryHandler     $categoryViewQueryHandler
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        DayRepositoryInterface $dayRepository,
        SpeakerViewQueryHandler $speakerViewQueryHandler,
        CategoryViewQueryHandler $categoryViewQueryHandler
    ) {
        $this->happeningRepository      = $happeningRepository;
        $this->dayRepository            = $dayRepository;
        $this->speakerViewQueryHandler  = $speakerViewQueryHandler;
        $this->categoryViewQueryHandler = $categoryViewQueryHandler;
    }

    /**
     * @param HappeningViewQuery $query
     *
     * @return HappeningListView
     *
     * @throws MissingEventDayConfigurationException
     */
    public function handle(HappeningViewQuery $query)
    {
        $eventDay = $this->dayRepository->findFirstDayByEvent($query->event);

        if ($eventDay === null) {
            throw new MissingEventDayConfigurationException();
        }

        $happenings = $this->happeningRepository->findByEventAndDayAndCategory(
            $query->event,
            $eventDay->getDay(),
            null
        );

        $happeningViews = [];

        foreach ($happenings as $key => $happening) {
            $happeningCategoryView = $this->categoryViewQueryHandler->handle(
                new CategoryViewQuery($happening, $query->locale)
            );

            $speakerView = $this->speakerViewQueryHandler->handle(
                new SpeakerViewQuery($happening, $query->locale)
            );

            $happeningView = new HappeningView(
                $key + 1,
                $happeningCategoryView,
                $happening->getBegin(),
                $happening->getEnd(),
                $happening->getTitle($query->locale),
                $happening->getDescription($query->locale),
                '',
                $speakerView
            );

            $happeningViews[] = $happeningView;
        }

        return new HappeningListView(
            $query->event,
            $eventDay->getStartTime(),
            $eventDay->getEndTime(),
            $query->event->getConfiguration()->getScheduleScale(),
            $happeningViews
        );
    }
}
