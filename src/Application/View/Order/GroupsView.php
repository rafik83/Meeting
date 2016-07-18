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
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->groups, function ($carry, GroupView $group) {
            return $carry + $group->getTotal();
        }, 0);
    }

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
