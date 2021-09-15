<?php

namespace Proximum\Vimeet\Application\Query\Badge;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class AbstractGetBadgeByEventQuery implements Query
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
