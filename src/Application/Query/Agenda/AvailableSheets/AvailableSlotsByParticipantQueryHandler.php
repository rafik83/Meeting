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

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
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

        $availableSlotViews = [];

        foreach ($availableSlots as $availableSlot) {
            $availableSlotViews[] = new AvailableSlotView(
                $availableSlot->getId(),
                $availableSlot->getBegin(),
                $availableSlot->duration()
            );
        }

        return $availableSlotViews;
    }
}
