<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;

class TimeSlotDispatcher
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var MassRepositoryInterface
     */
    private $massRepository;

    /**
     * @var MassAssignmentRepositoryInterface
     */
    private $massAssignmentRepository;

    /**
     * TimeSlotDispatcher constructor.
     *
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param MassRepositoryInterface           $massRepository
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        MassRepositoryInterface $massRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository
    ) {
        $this->participantRepository    = $participantRepository;
        $this->massRepository           = $massRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
    }

    /**
     * Dispatch time slots of a mass unavailbilty between all participants of the event
     *
     * @param Mass $mass
     *
     * @throws UnableToDispatchException
     */
    public function dispatch(Mass $mass)
    {
        if (!$mass->isDispatch()) {
            throw new UnableToDispatchException('Dispatch is not enabled on this mass unavailability.');
        }

        $timeSlots = $mass->getTimeSlots();

        if (empty($timeSlots)) {
            throw new UnableToDispatchException('No time slot available on this mass unavailability.');
        }

        $participants = $this->participantRepository->findByEventWithoutDispatch($mass->getEvent(), $mass);

        foreach ($participants as $index => $participant) {
            $timeSlot   = $timeSlots[$index % count($timeSlots)];
            $assignment = new MassAssignment($mass, $participant, $timeSlot->getFrom(), $timeSlot->getTo());

            $this->massAssignmentRepository->add($assignment);
        }
    }

    /**
     * @param Event $event
     *
     * @throws UnableToDispatchException
     */
    public function dispatchAll(Event $event)
    {
        $unavailabilities = $this->massRepository->findDispatchByEvent($event);

        foreach ($unavailabilities as $unavailability) {
            $this->dispatch($unavailability);
        }
    }
}
