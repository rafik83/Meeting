<?php

namespace Proximum\Vimeet\Domain\Model;

class SpotUnavailability
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var MeetingSlot
     */
    private $slot;

    /**
     * @var Spot
     */
    private $spot;

    /**
     * SpotUnavailability constructor.
     *
     * @param MeetingSlot $slot
     * @param Spot        $spot
     */
    public function __construct(MeetingSlot $slot, Spot $spot)
    {
        $this->slot = $slot;
        $this->spot = $spot;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MeetingSlot
     */
    public function getSlot()
    {
        return $this->slot;
    }

    /**
     * @return Spot
     */
    public function getSpot()
    {
        return $this->spot;
    }
}
