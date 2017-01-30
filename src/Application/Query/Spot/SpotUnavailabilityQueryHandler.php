<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;

class SpotUnavailabilityQueryHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @var SpotUnavailabilityRepositoryInterface
     */
    private $spotUnavailabilityRepository;

    /**
     * SpotUnavailabilityQueryHandler constructor.
     *
     * @param SpotRepositoryInterface               $spotRepository
     * @param SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
    ) {
        $this->spotRepository               = $spotRepository;
        $this->spotUnavailabilityRepository = $spotUnavailabilityRepository;
    }

    /**
     * @param SpotUnavailabilityQuery $query
     */
    public function handle(SpotUnavailabilityQuery $query)
    {
        $spots = $this->spotRepository->find($query->event, $query->spots);

        $spotUnavailabilities = [];

        foreach ($spots as $spot) {
            $unavailabilities = $this->spotUnavailabilityRepository->findBySpot($spot);

            foreach ($unavailabilities as $unavailability) {
                $spotUnavailabilities[$spot->getId()] = $unavailability->getId();
            }
        }
    }
}
