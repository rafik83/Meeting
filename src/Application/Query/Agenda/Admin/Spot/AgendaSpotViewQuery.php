<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Spot;

class AgendaSpotViewQuery
{
    /**
     * @var Spot
     */
    public $spot;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * AgendaSpotViewQuery constructor.
     *
     * @param Spot   $spot
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Spot $spot, Event $event, $locale)
    {
        $this->spot   = $spot;
        $this->event  = $event;
        $this->locale = $locale;
    }
}
