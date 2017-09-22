<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Application\Exception\Event\NoEventOnCurrentDayException;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class ReminderHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param EventRepositoryInterface     $eventRepository
     * @param UserRepositoryInterface      $userRepository
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        UserRepositoryInterface $userRepository,
        ExtraDataRepositoryInterface $extraDataRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(Reminder $command)
    {
        $currentEvents = $this->eventRepository->findByDay($command->dateTime);

        if (empty($currentEvents)) {
            throw new NoEventOnCurrentDayException('There is no event today');
        }

        $usersWithValidatedPhoneAndPendingRequest = $this
            ->userRepository
            ->getUsersByEventsWithValidatedPhoneNumberAndPendingRequest($currentEvents)
        ;
    }
}
