<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateVisioHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * VisioHandler constructor.
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param UpdateVisio $updateVisio
     */
    public function handle(UpdateVisio $updateVisio)
    {
        $participant = $updateVisio->participant;

        $participant->setVisio($updateVisio->visio);

        $this->participantRepository->set($participant);
    }
}
