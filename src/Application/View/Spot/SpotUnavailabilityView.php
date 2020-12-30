<?php

namespace Proximum\Vimeet\Application\View\Spot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;

class SpotUnavailabilityView
{
    /**
     * Array of spotId => SpotUnavailability[]
     *
     * @var array
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
        if (0 === count($this->spotUnavailabilities)) {
            return true;
        }

        $pattern = array_map(function (SpotUnavailability $unavailability) {
            return $unavailability->getSlot()->getId();
        }, reset($this->spotUnavailabilities));

        $i = 0;
        foreach ($this->spotUnavailabilities as $unavailabilities) {
            // prevent checking the first element because its the pattern
            if (0 === $i) {
                ++$i;
                continue;
            }

            $unavailabilitiesIds = array_map(function (SpotUnavailability $unavailability) {
                return $unavailability->getSlot()->getId();
            }, $unavailabilities);

            // check if same spot unavailabilities using ids diff
            $diff = array_diff($unavailabilitiesIds, $pattern);

            // unavailabilities and pattern are different or unavailabilities are empty
            if (0 !== count($diff) || 0 === count($unavailabilitiesIds)) {
                return false;
            }

            ++$i;
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
        if (null === $this->getUnavailabilities()) {
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
        if (0 === count($this->spotUnavailabilities)) {
            return null;
        }

        return reset($this->spotUnavailabilities);
    }
}
