<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event;

class TypeViewQuery
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
     * @param Event  $event
     * @param string $locale
     */
    public function __construct(Event $event, $locale)
    {
        $this->event  = $event;
        $this->locale = $locale;
    }
}
