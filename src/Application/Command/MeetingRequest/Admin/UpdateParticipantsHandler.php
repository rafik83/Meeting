<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest\Admin;

use Proximum\Vimeet\Application\Exception\MeetingRequest\InvalidParticipantException;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateParticipantsHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param RequestRepositoryInterface     $requestRepository
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->requestRepository     = $requestRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param UpdateParticipants $updateParticipants
     *
     * @throws InvalidParticipantException
     */
    public function handle(UpdateParticipants $updateParticipants)
    {
        $fromParticipants = $this->participantRepository->findByIds($updateParticipants->fromParticipants);
        $toParticipants   = $this->participantRepository->findByIds($updateParticipants->toParticipants);

        foreach ($fromParticipants as $fromParticipant) {
            if ($fromParticipant->getSheet() !== $updateParticipants->request->getFromSheet()) {
                throw new InvalidParticipantException(sprintf(
                    'this participant %s is not present on the %s sheet',
                    $fromParticipant->getId(),
                    $updateParticipants->request->getFromSheet()->getId()
                ));
            }
        }

        foreach ($toParticipants as $toParticipant) {
            if ($toParticipant->getSheet() !== $updateParticipants->request->getToSheet()) {
                throw new InvalidParticipantException(sprintf(
                    'this participant %s is not present on the %s sheet',
                    $toParticipant->getId(),
                    $updateParticipants->request->getToSheet()->getId()
                ));
            }
        }

        $updateParticipants->request->updateFromParticipants($fromParticipants);
        $updateParticipants->request->updateToParticipants($toParticipants);

        $this->requestRepository->set($updateParticipants->request);
    }
}
