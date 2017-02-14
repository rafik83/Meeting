<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\MassAssignment;

use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class UpdateHandler
{
    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param ParticipantRepositoryInterface    $participantRepository
     */
    public function __construct(
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->participantRepository    = $participantRepository;
    }

    /**
     * @param Update $update
     *
     * @throws MassAssignmentOnMeetingException
     * @throws MassAssignmentOutOfMassSlotException
     */
    public function handle(Update $update)
    {
        // if set to disable, only update enabled state
        if ($update->enabled === false) {
            $update->massAssignment->disable();
            $this->massAssignmentRepository->set($update->massAssignment);

            return;
        }

        $hasMeetingOrHappening = $this->participantRepository->getAvailableParticipants(
            [$update->massAssignment->getParticipant()],
            $update->begin,
            $update->end
        );

        if (count($hasMeetingOrHappening) > 0) {
            throw new MassAssignmentOnMeetingException();
        }

        if ($update->begin < $update->massAssignment->getMass()->getBegin()
            || $update->end > $update->massAssignment->getMass()->getEnd()
        ) {
            throw new MassAssignmentOutOfMassSlotException();
        }

        $update->massAssignment->update(
            $update->begin,
            $update->end,
            $update->enabled
        );

        $this->massAssignmentRepository->set($update->massAssignment);
    }
}
