<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Unavailability;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassAssignmentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Unavailability\MassRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\Exception\UnableToDispatchException;

class TimeSlotDispatcher
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var MassRepositoryInterface */
    private $massRepository;

    /** @var MassAssignmentRepositoryInterface */
    private $massAssignmentRepository;

    /** @var User[] */
    private $usersDispatched = [];

    /** @var JobQueueInterface */
    private $jobQueueInterface;

    /**
     * TimeSlotDispatcher constructor.
     *
     * @param UserRepositoryInterface           $userRepository
     * @param MassRepositoryInterface           $massRepository
     * @param MassAssignmentRepositoryInterface $massAssignmentRepository
     * @param JobQueueInterface                 $jobQueueInterface
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        MassRepositoryInterface $massRepository,
        MassAssignmentRepositoryInterface $massAssignmentRepository,
        JobQueueInterface $jobQueueInterface
    ) {
        $this->userRepository           = $userRepository;
        $this->massRepository           = $massRepository;
        $this->massAssignmentRepository = $massAssignmentRepository;
        $this->jobQueueInterface        = $jobQueueInterface;
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

        $users = $this->userRepository->findByEventWithoutDispatch($mass->getEvent(), $mass);

        foreach ($users as $index => $user) {
            $timeSlot   = $timeSlots[$index % \count($timeSlots)];
            $assignment = new MassAssignment(
                $mass,
                $user,
                $timeSlot->getFrom(),
                $timeSlot->getTo()
            );

            $this->massAssignmentRepository->add($assignment);

            $this->usersDispatched[$user->getId()] = $user;
        }
    }

    /**
     * @param Event $event
     *
     * @throws UnableToDispatchException
     */
    public function dispatchAll(Event $event)
    {
        $this->usersDispatched = [];

        $unavailabilities = $this->massRepository->findDispatchByEvent($event);

        foreach ($unavailabilities as $unavailability) {
            $this->dispatch($unavailability);
        }

        if (!empty($this->usersDispatched)) {
            $this->jobQueueInterface->aggregateUsersFullUnavailability($event, $this->usersDispatched);
        }
    }
}
