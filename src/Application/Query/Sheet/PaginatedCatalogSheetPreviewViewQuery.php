<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class PaginatedCatalogSheetPreviewViewQuery
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
     * @var Sheet
     */
    public $viewer;

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param Sheet  $viewer
     */
    public function __construct(Event $event, array $filters, $page, $limit, $locale, Sheet $viewer)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->page    = $page;
        $this->limit   = $limit;
        $this->locale  = $locale;
        $this->viewer  = $viewer;
    }
}
