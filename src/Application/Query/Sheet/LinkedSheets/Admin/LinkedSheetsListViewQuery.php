<?php

namespace Proximum\Vimeet\Application\Query\Sheet\LinkedSheets\Admin;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class LinkedSheetsListViewQuery implements Query
{
    /** @var Event */
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }
}
