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
    private $groupId;

    /** @var string */
    private $groupTitle;

    /** @var SheetView[] */
    private $sheetViews;

    /**
     * @param int         $groupId
     * @param string      $groupTitle
     * @param SheetView[] $sheetViews
     */
    public function __construct($groupId, $groupTitle, array $sheetViews)
    {
        $this->groupId    = $groupId;
        $this->groupTitle = $groupTitle;
        $this->sheetViews = $sheetViews;
    }
}
