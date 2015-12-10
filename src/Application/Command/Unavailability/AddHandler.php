<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class AddHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * AddUnavailabilityHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(UnavailabilityRepositoryInterface $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * @param Add $addUnavailability
     */
    public function handle(Add $addUnavailability)
    {
        foreach ($addUnavailability->participants as $participant) {
            $unavailability = $this->createUnavailability($addUnavailability, $participant);
            $this->mergeOverlapUnavailabilities($unavailability);
            $this->unavailabilityRepository->add($unavailability);
        }
    }

    /**
     * @param Add         $addUnavailability
     * @param Participant $participant
     *
     * @return Unavailability
     */
    private function createUnavailability(Add $addUnavailability, Participant $participant)
    {
        $unavailability = new Unavailability(
            $addUnavailability->schedule,
            $participant,
            $addUnavailability->from,
            $addUnavailability->to
        );

        return $unavailability;
    }

    /**
     * @param Unavailability $unavailability
     */
    private function mergeOverlapUnavailabilities(Unavailability $unavailability)
    {
        // Here clone is required because of a bug in phophecy making test impossible
        // See https://github.com/phpspec/prophecy/issues/75
        $overlapUnavailabilities = $this->unavailabilityRepository->getOverlapUnavailabilities(clone $unavailability);

        foreach ($overlapUnavailabilities as $overlapUnavailability) {
            $unavailability->merge($overlapUnavailability);
            $this->unavailabilityRepository->remove($overlapUnavailability);
        }
    }
}
