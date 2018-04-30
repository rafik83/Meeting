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

    /**
     * @param UpdateVisio $updateVisio
     *
     * @throws ParticipantException
     */
    public function handle(UpdateVisio $updateVisio)
    {
        $participant = $updateVisio->participant;

        $participant->setVisio($updateVisio->visio);

        $this->participantRepository->set($participant);
    }
}
