<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class LastEventParticipation
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository, \DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param User $user
     * @param Event $currentEvent
     *
     * @return null|Participant
     */
    public function getLastEvent(User $user, Event $currentEvent): ?Participant
    {
        // TODO: currentEvent getDuplicateFrom

        $participant = $this->participantRepository->getLastEventParticipation($user, $currentEvent);

        return $participant;
    }
}
