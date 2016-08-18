<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class GroupsView
{
    /**
     * @var GroupView[]
     */
    public $groups = [];

    /**
     * @param GroupView $group
     *
     * @return GroupsView
     */
    public function addGroupView(GroupView $group)
    {
        $this->groups[] = $group;

        return $this;
    }
}
