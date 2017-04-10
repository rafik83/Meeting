<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Group;

class GroupView
{
    /** @var string */
    public $title;

    /** @var SheetView[] */
    public $sheetViews;

    /**
     * @param string $title
     * @param array  SheetView[]
     */
    public function __construct($title, array $sheetViews)
    {
        $this->title = $title;
        $this->sheetViews = $sheetViews;
    }
}
