<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\MeetingPropositionFromAvailableSheets;

use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class MeetingPropositionFromAvailableSheetsQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->requestRepository = $requestRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param MeetingPropositionFromAvailableSheetsQuery $meetingPropositionFromAvailableSheetsQuery
     *
     * @return int
     */
    public function handle(MeetingPropositionFromAvailableSheetsQuery $meetingPropositionFromAvailableSheetsQuery): int
    {
        $pendingMeetingRequests = $this->requestRepository->getPendingPropositionReceivedBySheet(
            $meetingPropositionFromAvailableSheetsQuery->sheet
        );

        $participants = [];

        foreach ($pendingMeetingRequests as $pendingMeetingRequest) {
            foreach ($pendingMeetingRequest->getFromSheet()->getParticipants() as $participant) {
                $participants[] = $participant;
            }
        }

        $availableParticipants = $this->participantRepository->getAvailableParticipants(
            $participants,
            $meetingPropositionFromAvailableSheetsQuery->meetingSlot->getBegin(),
            $meetingPropositionFromAvailableSheetsQuery->meetingSlot->getEnd()
        );

        $filteredAvailableSheets = [];

        foreach ($availableParticipants as $availableParticipant) {
            $filteredAvailableSheets[$availableParticipant->getSheet()->getId()] = $availableParticipant->getSheet();
        }

        return count($filteredAvailableSheets);
    }
}
