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

class AddUnavailabilityHandler
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
     * @param AddUnavailability $addUnavailability
     */
    public function handle(AddUnavailability $addUnavailability)
    {
        foreach ($addUnavailability->participants as $participant) {
            $unavailability = $this->createUnavailability($addUnavailability, $participant);
            $this->mergeOverlapUnavailabilities($unavailability);
            $this->unavailabilityRepository->add($unavailability);
        }
    }

    /**
     * @param AddUnavailability $addUnavailability
     * @param Participant       $participant
     *
     * @return Unavailability
     */
    private function createUnavailability(AddUnavailability $addUnavailability, Participant $participant)
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
     * @param $unavailability
     */
    private function mergeOverlapUnavailabilities(Unavailability $unavailability)
    {
        $overlapUnavailabilities = $this->unavailabilityRepository->getOverlapUnavailabilities($unavailability);

        foreach ($overlapUnavailabilities as $overlapUnavailability) {
            if ($overlapUnavailability->getBegin() < $unavailability->getBegin()) {
                $unavailability->setBegin($overlapUnavailability->getBegin());
            }

            if ($overlapUnavailability->getEnd() > $unavailability->getEnd()) {
                $unavailability->setEnd($overlapUnavailability->getEnd());
            }

            $this->unavailabilityRepository->remove($overlapUnavailability);
        }
    }
}
