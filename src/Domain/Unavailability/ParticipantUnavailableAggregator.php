<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantUnavailableAggregator
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participant $participant
     */
    public function aggregateUnavailability(Participant $participant)
    {
        $event  = $participant->getSheet()->getEvent();
        $slots  = $this->meetingSlotRepository->findAvailableSlotsByParticipantsIds($event, [$participant->getId()]);

        $participant->setIsFullyUnavailable(empty($slots));

        $this->participantRepository->set($participant);
    }
}
