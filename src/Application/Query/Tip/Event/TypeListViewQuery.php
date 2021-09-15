<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;

class TypeListViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /**
     * TypeListViewQuery constructor.
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
