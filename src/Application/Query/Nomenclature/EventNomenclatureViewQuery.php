<?php

namespace Proximum\Vimeet\Application\Query\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;

class EventNomenclatureViewQuery
{
    /** @var Event */
    public $event;

    /**
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
