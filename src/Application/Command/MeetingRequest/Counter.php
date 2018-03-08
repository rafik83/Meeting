<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class Counter
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var AvailableSlotsByParticipantQueryHandler */
    private $availableSlotsByParticipantQueryHandler;

    /**
     * @param AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler
     * @param RequestRepositoryInterface              $requestRepository
     */
    public function __construct(
        AvailableSlotsByParticipantQueryHandler $availableSlotsByParticipantQueryHandler,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->requestRepository                       = $requestRepository;
        $this->availableSlotsByParticipantQueryHandler = $availableSlotsByParticipantQueryHandler;
    }

    /**
     * Return the count of pending meeting request with available slot
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return int
     */
    public function getCountAvailablePendingMeetingRequests(Sheet $sheet, Participant $participant): int
    {
        $slotAvailableViews = $this->availableSlotsByParticipantQueryHandler->handle(
            new AvailableSlotsByParticipantQuery($sheet->getEvent(), $participant)
        );

        $slotIds = array_map(
            function (AvailableSlotView $availableSlotView) {
                return $availableSlotView->id;
            },
            $slotAvailableViews
        );

        return $this->requestRepository->countPendingPropositionReceivedBySheetWithAvailableToSheet(
            $sheet,
            $slotIds
        );
    }
}
