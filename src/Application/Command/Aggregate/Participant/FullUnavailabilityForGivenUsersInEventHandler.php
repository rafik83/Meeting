<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Aggregate\Participant;

use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class FullUnavailabilityForGivenUsersInEventHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ParticipantUnavailableAggregator */
    private $participantUnavailableAggregator;

    /**
     * @param ParticipantRepositoryInterface   $participantRepository
     * @param UserRepositoryInterface          $userRepository
     * @param ParticipantUnavailableAggregator $participantUnavailableAggregator
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        UserRepositoryInterface $userRepository,
        ParticipantUnavailableAggregator $participantUnavailableAggregator
    ) {
        $this->participantRepository = $participantRepository;
        $this->userRepository = $userRepository;
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
    }

    /**
     * @param FullUnavailabilityForGivenUsersInEvent $fullUnavailabilityForGivenUsersInEvent
     */
    public function handle(FullUnavailabilityForGivenUsersInEvent $fullUnavailabilityForGivenUsersInEvent)
    {
        $event = $fullUnavailabilityForGivenUsersInEvent->event;
        $users = $this->userRepository->getByIdsIndexedById($fullUnavailabilityForGivenUsersInEvent->userIds);

        foreach ($users as $user) {
            $this->participantUnavailableAggregator->aggregateUnavailability($user, $event);
        }
    }
}
