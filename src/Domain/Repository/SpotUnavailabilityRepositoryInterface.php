<?php

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
     */
    public function remove(Spot $spot);

    /**
     * @param Spot $spot
     *
     * @return SpotUnavailability[]
     */
    public function findBySpot(Spot $spot);
}
