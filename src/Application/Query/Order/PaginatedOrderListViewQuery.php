<?php

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class PaginatedOrderListViewQuery implements Query
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $filters;

    /**
     * @var int
     */
    public $page;

    /**
     * @var int
     */
    public $limit;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     */
    public function __construct(Event $event, array $filters, $page, $limit, $locale)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->page    = $page;
        $this->limit   = $limit;
        $this->locale  = $locale;
    }
}
