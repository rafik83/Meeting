<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Request;

class SheetListView
{
    /** @var int */
    public $groupId;

    /** @var string */
    public $groupTitle;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @var int
     */
    public $page;

    /**
     * Number of pages
     *
     * @var int
     */
    public $pages;

    /**
     * @param int         $groupId
     * @param string      $groupTitle
     * @param SheetView[] $sheetViews
     * @param int         $page
     * @param int         $pages
     */
    public function __construct($groupId, $groupTitle, array $sheetViews, $page, $pages)
    {
        $this->groupId    = $groupId;
        $this->groupTitle = $groupTitle;
        $this->sheetViews = $sheetViews;
        $this->page       = $page;
        $this->pages      = $pages;
    }
}
