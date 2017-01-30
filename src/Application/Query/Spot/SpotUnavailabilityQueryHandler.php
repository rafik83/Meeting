<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Application\View\Spot\SpotUnavailabilityView;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotUnavailabilityQueryHandler
{
    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * SpotUnavailabilityQueryHandler constructor.
     *
     * @param SpotRepositoryInterface $spotRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository)
    {
        $this->spotRepository = $spotRepository;
    }

    /**
     * @param SpotUnavailabilityQuery $query
     *
     * @return SpotUnavailabilityView
     */
    public function handle(SpotUnavailabilityQuery $query)
    {
        $spots = $this->spotRepository->find($query->event, $query->spots);

        $spotUnavailabilities = [];

        foreach ($spots as $spot) {
            $unavailabilities = $spot->getSpotUnavailabilities();

            $spotUnavailabilities[$spot->getId()] = $unavailabilities;
        }

        return new SpotUnavailabilityView($spotUnavailabilities);
    }
}
