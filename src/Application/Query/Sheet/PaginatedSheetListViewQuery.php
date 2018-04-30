<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class PaginatedSheetListViewQuery
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
     * @var Admin
     */
    public $admin;

    /**
     * PaginatedSheetListViewQuery constructor.
     *
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     * @param Admin  $admin
     */
    public function __construct(Event $event, array $filters, $page, $limit, $locale, Admin $admin)
    {
        $this->event   = $event;
        $this->filters = $filters;
        $this->page    = $page;
        $this->limit   = $limit;
        $this->locale  = $locale;
        $this->admin   = $admin;
    }
}
