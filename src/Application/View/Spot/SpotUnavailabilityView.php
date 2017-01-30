<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Spot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;

class SpotUnavailabilityView
{
    /**
     * Array of spotId => SpotUnavailability[]
     *
     * @var array $unavailabilities
     */
    private $spotUnavailabilities;

    /**
     * SpotUnavailabilityView constructor.
     *
     * @param array $spotUnavailabilities
     */
    public function __construct(array $spotUnavailabilities)
    {
        $this->spotUnavailabilities = $spotUnavailabilities;
    }

    /**
     * @return bool
     */
    public function isSameUnavailabilities()
    {
        if (count($this->spotUnavailabilities) === 0) {
            return false;
        }

        $pattern = array_map(function (SpotUnavailability $unavailability) {
            return $unavailability->getId();
        }, reset($this->spotUnavailabilities));

        foreach ($this->spotUnavailabilities as $unavailabilities) {
            $unavailabilitiesIds = array_map(function (SpotUnavailability $unavailability) {
                return $unavailability->getId();
            }, $unavailabilities);

            $diff = array_diff($pattern, $unavailabilitiesIds);

            if (count($diff) !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get meeting slots of an array of SpotUnavailabilities
     *
     * @return MeetingSlot[]|null
     */
    public function getMeetingSlots()
    {
        if ($this->getUnavailabilities() === null) {
            return [];
        }

        $slots = [];
        foreach ($this->getUnavailabilities() as $unavailability) {
            $slots[] = $unavailability->getSlot();
        }

        return $slots;
    }

    /**
     * @return SpotUnavailability[]|null
     */
    private function getUnavailabilities()
    {
        if (count($this->spotUnavailabilities) === 0) {
            return null;
        }

        return reset($this->spotUnavailabilities);
    }
}
