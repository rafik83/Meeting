<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Unavailability;

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

        $this->unavailabilityRepository->set($unavailability);
    }
}
