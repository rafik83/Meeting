<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;

interface SpotUnavailabilityRepositoryInterface
{
    /**
     * @param SpotUnavailability $spotUnavailability
     */
    public function add(SpotUnavailability $spotUnavailability);

    /**
     * @param Spot $spot
     *
     * @return Spot[]
     */
    public function findBySpot(Spot $spot);
}
