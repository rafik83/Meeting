<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Spot;

class DaySpotViewQuery
{
    /**
     * @var Day
     */
    public $day;

    /**
     * @var int
     */
    public $dayNumber;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var string
     */
    public $locale;

    /**
     * DaySpotViewQuery constructor.
     *
     * @param Day    $day
     * @param int    $dayNumber
     * @param Event  $event
     * @param Spot   $spot
     * @param string $locale
     */
    public function __construct(Day $day, $dayNumber, Event $event, Spot $spot, $locale)
    {
        $this->day       = $day;
        $this->dayNumber = $dayNumber;
        $this->event     = $event;
        $this->spot      = $spot;
        $this->locale    = $locale;
    }
}
