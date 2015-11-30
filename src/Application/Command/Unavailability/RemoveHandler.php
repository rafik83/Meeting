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

class RemoveHandler
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     */
    public function __construct(UnavailabilityRepositoryInterface $unavailabilityRepository)
    {
        $this->unavailabilityRepository = $unavailabilityRepository;
    }

    /**
     * @param Remove $remove
     */
    public function handle(Remove $remove)
    {
        $this->unavailabilityRepository->remove($remove->unavailability);
    }
}
