<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\AvailableSheets;

use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class AvailableSlotsByParticipantQueryHandler
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param AvailableSlotsByParticipantQuery $query
     *
     * @return AvailableSlotView[]
     */
    public function handle(AvailableSlotsByParticipantQuery $query): array
    {
        $timeZone = new \DateTimeZone($query->event->getTimeZone());
        $currentDate = $this->dateTime->setTimeZone($timeZone);

        foreach ($query->event->getDays() as $day) {
            $dayDate = $day->getDay()->setTimeZone(new \DateTimeZone($query->event->getTimeZone()));

            if ($dayDate->format('d/m/Y') !== $currentDate->format('d/m/Y')) {
                return [];
            }
        }

        $availableSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->event, [$query->participant]
        );

        foreach ($availableSlots as $availableSlotKey => $availableSlot) {
            $beginHourPlusTenMinutes = $availableSlot->getBegin()->add(new \DateInterval('PT10M'))->setTimeZone($timeZone);

            if ($currentDate >= $beginHourPlusTenMinutes) {
                unset($availableSlots[$availableSlotKey]);
            }
        }

        $availableSlotViews = [];

        foreach ($availableSlots as $availableSlot) {
            $availableSlotViews[] = new AvailableSlotView(
                $availableSlot->getId(),
                $availableSlot->getBegin(),
                $availableSlot->getEnd()
            );
        }

        return $availableSlotViews;
    }
}
