<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;

class SlotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var Event\Day
     */
    public $day;

    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var string
     */
    public $locale;

    /**
     * SlotViewQuery constructor.
     *
     * @param Event     $event
     * @param Event\Day $day
     * @param Spot      $spot
     * @param string    $locale
     */
    public function __construct(Event $event, Event\Day $day, Spot $spot, $locale)
    {
        $this->event  = $event;
        $this->day    = $day;
        $this->spot   = $spot;
        $this->locale = $locale;
    }
}
