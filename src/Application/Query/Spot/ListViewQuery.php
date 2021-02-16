<?php

namespace Proximum\Vimeet\Application\Query\Spot;

use Proximum\Vimeet\Domain\Model\Event;

class ListViewQuery
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
     * @var array
     */
    public $filters;

    /**
     * @param Event  $event
     * @param string $locale
     * @param array  $filters
     */
    public function __construct(Event $event, $locale, array $filters)
    {
        $this->event   = $event;
        $this->locale  = $locale;
        $this->filters = $filters;
    }
}
