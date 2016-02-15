<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Sheet\DataConstraintChecker;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\UnknownParticipantException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class UpdateHandler
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
     * @param Update $update
     *
     * @throws UnknownParticipantException
     * @throws RequiredDataEmptyException
     */
    public function handle(Update $update)
    {
        $participant = $this->participantRepository->findById($update->id);

        if ($participant === null) {
            throw new UnknownParticipantException();
        }

        // Check the constraint on the data (required)
        (new DataConstraintChecker())->check($update->data, $participant->getSheet()->getType()->getParticipantTemplate());

        $participant->setData($update->data);

        $this->participantRepository->set($participant);
    }
}
