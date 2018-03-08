<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\Event\DayRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UpdateEventDatesToCurrentDateHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var MassAssignmentRepositoryInterface */
    private $massAssignmentRepository;

    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var bool */
    private $featureEnabled;

    /** @var DayRepositoryInterface */
    private $dayRepository;

    /**
     * @param EventRepositoryInterface          $eventRepository
     * @param MeetingSlotRepositoryInterface    $meetingSlotRepository
     * @param \DateTimeInterface                $dateTime
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param HappeningRepositoryInterface      $happeningRepository
     * @param MassRepositoryInterface           $massRepository
     * @param bool                              $featureEnabled
     * @param DayRepositoryInterface            $dayRepository
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        \DateTimeInterface $dateTime,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        HappeningRepositoryInterface $happeningRepository,
        MassRepositoryInterface $massRepository,
        bool $featureEnabled,
        DayRepositoryInterface $dayRepository
    ) {
        $this->eventRepository          = $eventRepository;
        $this->meetingSlotRepository    = $meetingSlotRepository;
        $this->dateTime                 = $dateTime;
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->happeningRepository      = $happeningRepository;
        $this->massRepository           = $massRepository;
        $this->featureEnabled           = $featureEnabled;
        $this->dayRepository            = $dayRepository;
    }

    /**
     * @param UpdateEventDatesToCurrentDate $command
     */
    public function handle(UpdateEventDatesToCurrentDate $command)
    {
        if (!$this->featureEnabled) {
            throw new AccessDeniedHttpException('This feature is disabled');
        }

        $slots            = $this->meetingSlotRepository->findByEvent($command->event);
        $masses           = $this->massRepository->findByEvent($command->event);
        $massAssignments  = $this->massAssignmentRepository->findByEvent($command->event);
        $unavailabilities = $this->unavailabilityRepository->getByEvent($command->event);
        $happenings       = $this->happeningRepository->findByEvent($command->event);
        $days             = $this->dayRepository->findByEvent($command->event);

        $happeningsPerDay = $this->formatPerDay($happenings);
        foreach ($happeningsPerDay as $dayNumber => $happenings) {
            foreach ($happenings as $happening) {
                $happening->setBegin($this->update($happening->getBegin(), $dayNumber));
                $happening->setEnd($this->update($happening->getEnd(), $dayNumber));
                $this->happeningRepository->set($happening);
            }
        }

        $unavailabilitiesPerDay = $this->formatPerDay($unavailabilities);
        foreach ($unavailabilitiesPerDay as $dayNumber => $unavailabilities) {
            foreach ($unavailabilities as $unavailability) {
                $unavailability->update(
                    $this->update($unavailability->getBegin(), $dayNumber),
                    $this->update($unavailability->getEnd(), $dayNumber)
                );
                $this->unavailabilityRepository->set($unavailability);
            }
        }

        $massAssignmentsPerDay = $this->formatPerDay($massAssignments);
        foreach ($massAssignmentsPerDay as $dayNumber => $massAssignments) {
            foreach ($massAssignments as $massAssignment) {
                $massAssignment->update(
                    $this->update($massAssignment->getBegin(), $dayNumber),
                    $this->update($massAssignment->getEnd(), $dayNumber),
                    $massAssignment->isEnabled()
                );
                $this->massAssignmentRepository->set($massAssignment);
            }
        }

        $slotsPerDay = $this->formatPerDay($slots);
        foreach ($slotsPerDay as $dayNumber => $slots) {
            foreach ($slots as $slot) {
                $slot->setBegin($this->update($slot->getBegin(), $dayNumber));
                $slot->setEnd($this->update($slot->getEnd(), $dayNumber));
                $this->meetingSlotRepository->set($slot);
            }
        }

        foreach ($days as $dayNumber => $day) {
            $day->setStartTime($this->update($day->getStartTime(), $dayNumber));
            $day->setEndTime($this->update($day->getEndTime(), $dayNumber));
            $this->dayRepository->set($day);
        }

        $massesSortPerDay = $this->formatPerDay($masses);
        foreach ($massesSortPerDay as $dayNumber => $massesPerDay) {
            /** @var Mass $mass */
            foreach ($massesPerDay as $mass) {
                $mass->setDates(
                    $this->update($mass->getBegin(), $dayNumber),
                    $this->update($mass->getEnd(), $dayNumber)
                );
                $this->massRepository->update($mass);
            }
        }

        $command->event->getConfiguration()->setDates(
            $this->dateTime,
            $this->dateTime,
            $this->dateTime,
            null,
            null,
            null,
            $this->dateTime
        );

        $this->eventRepository->set($command->event);
    }

    /**
     * @param \DateTimeInterface $dayDateTime
     * @param int                $dayNumber
     *
     * @return \DateTimeInterface
     */
    private function update(\DateTimeInterface $dayDateTime, int $dayNumber = 0): \DateTimeInterface
    {
        $now = clone $this->dateTime;

        if ($dayNumber > 0) {
            $now = $now->modify('+' . $dayNumber . ' day');
        }

        return $now->setTime(
            $dayDateTime->format('H'),
            $dayDateTime->format('i'),
            $dayDateTime->format('s')
        );
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function formatPerDay(array $elements): array
    {
        $dayDates = [];
        $formattedDayDates = [];

        foreach ($elements as $element) {
            $dayDates[$element->getBegin()->format('d')][] = $element;
        }

        $dayNumber = 0;
        foreach ($dayDates as $day => $dates) {
            $formattedDayDates[$dayNumber] = $dayDates[$day];
            $dayNumber++;
        }

        return $formattedDayDates;
    }
}
