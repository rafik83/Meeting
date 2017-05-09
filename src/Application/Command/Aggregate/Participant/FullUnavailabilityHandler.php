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

class FullUnavailabilityHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var ParticipantUnavailableAggregator */
    private $participantUnavailableAggregator;

    /**
     * @param UserRepositoryInterface          $userRepository
     * @param ParticipantRepositoryInterface   $participantRepository
     * @param ParticipantUnavailableAggregator $participantUnavailableAggregator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        ParticipantUnavailableAggregator $participantUnavailableAggregator
    ) {
        $this->userRepository                   = $userRepository;
        $this->participantRepository            = $participantRepository;
        $this->participantUnavailableAggregator = $participantUnavailableAggregator;
    }

    /**
     * @param FullUnavailability $fullUnavailability
     */
    public function handle(FullUnavailability $fullUnavailability)
    {
        if ($fullUnavailability->onlyCatalog) {
            $users = $this->userRepository->findByEventAndInCatalog($fullUnavailability->event);
        } else {
            $users = $this->userRepository->findByEvent($fullUnavailability->event);
        }

        foreach ($users as $user) {
            $this->participantUnavailableAggregator->aggregateUnavailability($user, $fullUnavailability->event);
        }
    }
}
