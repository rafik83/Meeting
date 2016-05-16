<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\UpdateNotAllowedException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateHandler
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
     * UpdateHandler constructor.
     *
     * @param ParticipantRepositoryInterface $participantRepository
     * @param ParticipantManager             $participantManager
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        ParticipantManager $participantManager
    ) {
        $this->participantRepository = $participantRepository;
        $this->participantManager    = $participantManager;
    }

    /**
     * @param Update $update
     *
     * @throws UpdateNotAllowedException
     * @throws RequiredDataEmptyException
     */
    public function handle(Update $update)
    {
        // Check permission
        if (!$this->participantManager->isUserAllowedToEditParticipant($update->sheet, $update->participant, $update->requester)) {
            throw new UpdateNotAllowedException('You are not allowed to update this participant.');
        }

        // Update participant
        $update->participant->setData($update->data);
        $this->participantRepository->set($update->participant);
    }
}
