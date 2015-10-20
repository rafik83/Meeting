<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Participate $participate
     */
    public function handle(Participate $participate)
    {
        $data        = json_encode($participate->data);
        $participant = new Participant($participate->user, $participate->event, $participate->type, $data);

        $this->participantRepository->add($participant);

        $participate->participant = $participant;
    }
}
