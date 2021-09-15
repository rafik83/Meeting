<?php

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
