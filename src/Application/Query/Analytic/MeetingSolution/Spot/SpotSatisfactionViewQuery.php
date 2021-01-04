<?php

namespace Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot;

use Proximum\Vimeet\Domain\Model\Spot;

class SpotSatisfactionViewQuery
{
    /** @var int */
    public $numberOfMeeting;

    /** @var Spot */
    public $spot;

    /** @var int */
    public $numberOfSlotAvailable;

    /**
     * @param Spot $spot
     * @param int  $numberOfMeeting
     * @param int  $numberOfSlotAvailable
     */
    public function __construct(Spot $spot, int $numberOfMeeting, int $numberOfSlotAvailable)
    {
        $this->spot = $spot;
        $this->numberOfMeeting = $numberOfMeeting;
        $this->numberOfSlotAvailable = $numberOfSlotAvailable;
    }
}
