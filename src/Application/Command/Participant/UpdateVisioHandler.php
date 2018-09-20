<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Exception\Participant\ParticipantException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateVisioHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /**
     * VisioHandler constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function handle(UpdateVisio $updateVisio): void
    {
        $participants = $this->participantRepository->getAllParticipantForUser(
            $updateVisio->participant->getEvent(),
            $updateVisio->participant->getUser()
        );

        foreach ($participants as $participant) {
            $participant->setVisio($updateVisio->visio);
            $this->participantRepository->set($participant);
        }
    }
}
