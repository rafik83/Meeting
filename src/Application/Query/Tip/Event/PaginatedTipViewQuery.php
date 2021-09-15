<?php

namespace Proximum\Vimeet\Application\Query\Tip\Event;

use Proximum\Vimeet\Domain\Model\Event;

class PaginatedTipViewQuery
{
    /** @var Event */
    public $event;

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /**
     * PaginatedTipViewQuery constructor.
     *
     * @param Event $event
     * @param int   $page
     * @param int   $limit
     */
    public function __construct(Event $event, $page = 1, $limit = 20)
    {
        $this->event = $event;
        $this->page  = $page;
        $this->limit = $limit;
    }
}
