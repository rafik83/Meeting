<?php

namespace Proximum\Vimeet\Application\Query\Rooming\RoomingList;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class ListViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var Type[] */
    public $types;

    public function __construct(Event $event, string $locale, array $types)
    {
        $this->event = $event;
        $this->locale = $locale;
        $this->types = $types;
    }
}
