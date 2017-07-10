<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Catalog;

use Proximum\Vimeet\Domain\Model\Event;

class PaginatedSheetExternalViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var array
     */
    public $filters = [];

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
     * PaginatedSheetExternalViewQuery constructor.
     *
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     */
    public function __construct(Event $event, array $filters, int $page, int $limit, string $locale)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->page    = $page;
        $this->limit   = $limit;
        $this->locale  = $locale;
    }
}
