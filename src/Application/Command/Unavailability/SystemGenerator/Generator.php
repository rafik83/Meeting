<?php

/*
 * This file is part of the Proximumn Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability\SystemGenerator;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class Generator
{
    /** @var UnavailabilityRepositoryInterface */
    private $unavailabilityRepository;

    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        ParticipantRepositoryInterface $participantRepository
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->participantRepository = $participantRepository;
    }

    public function generateSystemUnavailability(Event $event, User $user): void
    {
        $this->unavailabilityRepository->removeSystemUnavailabilityForUserAndEvent($user, $event);

        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);

        if (empty($availabilityTimeRanges)) {
            return;
        }
    }
}
