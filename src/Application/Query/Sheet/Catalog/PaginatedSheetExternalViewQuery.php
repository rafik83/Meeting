<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Catalog;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;

class PaginatedSheetExternalViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var array */
    public $filters = [];

    /** @var int */
    public $page;

    /** @var int */
    public $limit;

    /** @var string */
    public $locale;

    /** @var bool */
    public $showCategory;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param bool   $showCategory
     */
    public function __construct(
        Event $event,
        array $filters,
        int $page,
        int $limit,
        string $locale,
        bool $showCategory = false
    ) {
        $this->event   = $event;
        $this->filters = $filters;
        $this->page    = $page;
        $this->limit   = $limit;
        $this->locale  = $locale;
        $this->showCategory = $showCategory;
    }
}
