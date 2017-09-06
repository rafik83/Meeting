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
        if ($query->event->getFirstDay()->getStartTime() > $this->dateTime
            || $query->event->getLastDay()->getEndTime() < $this->dateTime
        ) {
            return [];
        }

        $availableSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->event,
            [$query->participant]
        );

        $availableSlotViews = [];

        foreach ($availableSlots as $availableSlot) {
            $beginHour = clone $availableSlot->getBegin();
            $beginHourPlusTenMinutes = $beginHour->add(new \DateInterval('PT10M'));

            if ($beginHourPlusTenMinutes >= $this->dateTime
                && $this->dateTime->format('m/d/Y') === $query->day->getStartTime()->format('m/d/Y')
                && $this->dateTime->format('m/d/Y') === $availableSlot->getBegin()->format('m/d/Y')
            ) {
                $availableSlotViews[] = new AvailableSlotView(
                    $availableSlot->getId(),
                    $availableSlot->getBegin(),
                    $availableSlot->getEnd()
                );
            }
        }

        return $availableSlotViews;
    }
}
