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
use Proximum\Vimeet\Application\Exception\MeetingRequest\MissingEventDayConfigurationException;
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
     * HappeningViewQueryHandler constructor.
     *
     * @param HappeningRepositoryInterface $happeningRepository
     * @param DayRepositoryInterface       $dayRepository
     */
    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        DayRepositoryInterface $dayRepository
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->dayRepository       = $dayRepository;
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
        $eventDay = $this->dayRepository->findByEvent($query->event);

        if ($eventDay === null) {
            throw new MissingEventDayConfigurationException();
        }

        $happenings    = $this->happeningRepository->findByEvent($query->event);
        $scheduleScale = $query->event->getConfiguration()->getScheduleScale();

        $morningHappeningView   = [];
        $afternoonHappeningView = [];

        $middleDate = $this->getMiddleDate($scheduleScale, $eventDay);

        foreach ($happenings as $happening) {
            $happeningView = new HappeningView(
                $happening->getId(),
                $happening->getCategory()->getTitle($query->locale),
                $happening->getBegin(),
                $happening->getEnd(),
                $happening->getTitle($query->locale),
                $happening->getDescription($query->locale),
                '',
                $happening->getSpeakers()
            );

            if ($happening->getEnd()->format('H:i:s') <= $middleDate->format('H:i:s')) {
                $morningHappeningView[] = $happeningView;
            } else {
                $afternoonHappeningView[] = $happeningView;
            }

        }

        return new HappeningListView(
            $query->event,
            $eventDay->getStartTime(),
            $eventDay->getEndTime(),
            $middleDate,
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
