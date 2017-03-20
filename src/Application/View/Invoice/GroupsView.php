<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class GroupsView
{
    /**
     * @var GroupView[]
     */
    public $groupViews = [];

    /**
     * @param GroupView[] $groupViews
     */
    public function __construct(array $groupViews)
    {
        $this->groupViews = $groupViews;
    }
}
