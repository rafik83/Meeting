<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

class TipListViewQuery
{
    /** @var string */
    public $locale;

    /**
     * TipListViewQuery constructor.
     *
     * @param string $locale
     */
    public function __construct($locale)
    {
        $this->locale = $locale;
    }
}
