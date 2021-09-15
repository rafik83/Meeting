<?php

namespace Proximum\Vimeet\Application\View\Spot\Batch;

use Proximum\Vimeet\Domain\Model\Spot;

class DeleteBatchView
{
    /**
     * array of spot reference
     *
     * @var array
     */
    public $deletedSpots = [];

    /**
     * array of spot reference
     *
     * @var array
     */
    public $spotsWithMeetings = [];

    /**
     * array of spot reference
     *
     * @var array
     */
    public $spotsWithSheets = [];

    /**
     * @param Spot $spot
     */
    public function addDeletedSpot(Spot $spot)
    {
        $this->deletedSpots[$spot->getReference()] = $spot->getReference();
    }

    /**
     * @return string
     */
    public function getDeletedSpots()
    {
        return implode(', ', $this->deletedSpots);
    }

    /**
     * @param Spot $spot
     */
    public function addSpotWithMeeting(Spot $spot)
    {
        $this->spotsWithMeetings[$spot->getReference()] = $spot->getReference();
    }

    /**
     * @return string
     */
    public function getSpotsWithMeetings()
    {
        return implode(', ', $this->spotsWithMeetings);
    }

    /**
     * @param Spot $spot
     */
    public function addSpotWithSheets(Spot $spot)
    {
        $this->spotsWithSheets[$spot->getReference()] = $spot->getReference();
    }

    /**
     * @return string
     */
    public function getSpotsWithSheets()
    {
        return implode(', ', $this->spotsWithSheets);
    }
}
