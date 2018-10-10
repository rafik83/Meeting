<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class MeetingUpdateSlotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /**
     * @param SpotRepositoryInterface        $spotRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->spotRepository        = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param MeetingUpdateSlotViewQuery $query
     *
     * @return MeetingUpdateSlotView
     */
    public function handle(MeetingUpdateSlotViewQuery $query)
    {
        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->meeting->getEvent(),
            $query->meeting->getAllParticipants(),
            false,
            $query->meeting
        );

        $availableSlotsId = [];

        foreach ($slots as $slot) {
            if (true === $this->spotRepository->hasSpotsForSlotAndParticipantsQuantity(
                $slot,
                $query->meeting->countParticipants(),
                $query->meeting,
                $query->meeting->getFromSheet(),
                $query->meeting->getToSheet(),
                $query->visio
            )) {
                $availableSlotsId[] = $slot->getId();
            }
        }

        return new MeetingUpdateSlotView($availableSlotsId);
    }
}
