<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Happening;

use DateInterval;
use DatePeriod;
use Proximum\Vimeet\Application\Exception\Happening\MissingEventDayConfigurationException;
use Proximum\Vimeet\Application\View\Happening\HappeningCategoryView;
use Proximum\Vimeet\Application\View\Happening\HappeningListView;
use Proximum\Vimeet\Application\View\Happening\HappeningView;
use Proximum\Vimeet\Domain\Model\Event\Day;
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

        $scheduleScale = $query->event->getConfiguration()->getScheduleScale();

        $morningHappeningView   = [];
        $afternoonHappeningView = [];

        $middleDate = $this->getMiddleDate($scheduleScale, $eventDay);

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

            //if ($happening->getBegin()->format('H:i:s') <= $middleDate->format('H:i:s')) {
                $morningHappeningView[] = $happeningView;
            //}

            //if ($happening->getEnd()->format('H:i:s') > $middleDate->format('H:i:s')) {
               // $afternoonHappeningView[] = $happeningView;
            //}

        }

        return new HappeningListView(
            $query->event,
            $eventDay->getStartTime(),
            $eventDay->getEndTime(),
            $eventDay->getEndTime(),
            //$middleDate,
            $query->event->getConfiguration()->getScheduleScale(),
            $morningHappeningView,
            $afternoonHappeningView
        );
    }

    /**
     * @param int $scheduleScale (in minutes)
     * @param Day $eventDay
     *
     * @return \DateTimeInterface
     */
    private function getMiddleDate($scheduleScale, Day $eventDay)
    {
        $interval   = DateInterval::createFromDateString($scheduleScale . 'minutes');
        $period     = new DatePeriod($eventDay->getStartTime(), $interval, $eventDay->getEndTime());
        $totalSlots = iterator_count($period);

        $morningSlot = round($totalSlots / 2);
        $startTime   = clone $eventDay->getStartTime();

        $middleDayDate = $startTime->modify(($morningSlot * $scheduleScale) . 'minutes');

        return $middleDayDate;
    }
}
