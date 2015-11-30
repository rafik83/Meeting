<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class UpdateHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(UnavailabilityRepositoryInterface $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $unavailability = $update->unavailability;
        $unavailability->update($update->from, $update->to);

        $this->mergeOverlapUnavailabilities($unavailability);
        $this->unavailabilityRepository->set($unavailability);
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
