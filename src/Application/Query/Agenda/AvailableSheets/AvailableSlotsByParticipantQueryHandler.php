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
        $availableSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->event, [$query->participant]
        );

        // Todo ne garder que les slots du futur + 10mn

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
