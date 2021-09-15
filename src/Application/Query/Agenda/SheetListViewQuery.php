<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class SheetListViewQuery implements Query
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
     * @var bool
     */
    public $lazyLoadIndicators;

    /**
     * SheetListViewQuery constructor.
     *
     * @param Event  $event
     * @param string $locale
     * @param bool   $lazyLoadIndicators
     */
    public function __construct(Event $event, $locale, $lazyLoadIndicators = true)
    {
        $this->event              = $event;
        $this->locale             = $locale;
        $this->lazyLoadIndicators = $lazyLoadIndicators;
    }
}
