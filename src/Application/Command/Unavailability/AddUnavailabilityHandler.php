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
            $unavailability = new Unavailability(
                $addUnavailability->schedule,
                $participant,
                $addUnavailability->from,
                $addUnavailability->to
            );

            $this->unavailabilityRepository->add($unavailability);
        }
    }
}
