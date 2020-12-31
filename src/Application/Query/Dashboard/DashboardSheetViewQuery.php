<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Domain\Model\Event;

class DashboardSheetViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * DashboardSheetViewQuery constructor.
     *
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }
}
