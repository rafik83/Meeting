<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\ParticipantExtraDataRepositoryInterface;

class IsParticipantPresentToMeeting
{
    /** @var ParticipantExtraDataRepositoryInterface */
    private $participantExtraDataRepository;

    /** @var \DateTimeInterface */
    private $date;

    public function __construct(
        ParticipantExtraDataRepositoryInterface $participantExtraDataRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->participantExtraDataRepository = $participantExtraDataRepository;
        $this->date = $dateTime;
    }

    public function isSatisfiedBy(Participant $participant, Meeting $meeting): bool
    {
        if (false === $participant->isVisio()) {
            return false;
        }

        $participantExtraData = $this->participantExtraDataRepository->findOneByParticipantAndMeetingAndType(
            $participant,
            $meeting,
            Meeting\ParticipantExtraData::TYPE_PRESENCE
        );

        if (!$participantExtraData instanceof Meeting\ParticipantExtraData) {
            return false;
        }

        $limitDate = (new \DateTime())
            ->setTimestamp($this->date->getTimestamp())
            ->modify('-3 minutes');

        return $participantExtraData->getDate() >= $limitDate;
    }
}
