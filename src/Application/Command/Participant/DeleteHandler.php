<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Exception\Participant\DeleteNotAllowedException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class DeleteHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * DeleteHandler constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantManager             $participantManager
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository, ParticipantManager $participantManager)
    {
        $this->participantRepository = $participantRepository;
        $this->participantManager    = $participantManager;
    }

    /**
     * @param Delete $delete
     *
     * @throws DeleteNotAllowedException
     */
    public function handle(Delete $delete)
    {
        if (!$this->participantManager->isUserAllowedToDeleteParticipant($delete->sheet, $delete->participant, $delete->requester)) {
            throw new DeleteNotAllowedException('You are not allowed to delete this participant');
        }

        $delete->sheet->removeParticipant($delete->participant);
        $this->participantRepository->delete($delete->participant);
    }
}
