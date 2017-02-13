<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\MassAssignment;

use Proximum\Vimeet\Application\Exception\Meeting\DateFormatException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOnMeetingException;
use Proximum\Vimeet\Application\Exception\Unavailability\MassAssignmentOutOfMassSlotException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;

class UpdateHandler
{
    const DATE_FORMAT = 'd/m/Y H:i';

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
     * @throws DateFormatException
     * @throws MassAssignmentOnMeetingException
     * @throws MassAssignmentOutOfMassSlotException
     */
    public function handle(Update $update)
    {
        $beginTime = \DateTime::createFromFormat(self::DATE_FORMAT, $update->begin);
        $endTime = \DateTime::createFromFormat(self::DATE_FORMAT, $update->end);
        
        if ($beginTime === false || $endTime === false ) {
            throw new DateFormatException();
        }
        
        $hasMeetingOrHappening = $this->participantRepository->getAvailableParticipants(
            [$update->massAssignment->getParticipant()],
            $beginTime,
            $endTime
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
            $beginTime,
            $endTime,
            $update->enabled
        );

        $this->massAssignmentRepository->set($update->massAssignment);
    }
}
