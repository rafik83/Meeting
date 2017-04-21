<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Sheet\Group\Admin;

use Proximum\Vimeet\Application\View\Sheet\Group\Admin\GroupView;

class GroupListViewQueryHandler
{
    /** @var GroupView[] */
    private $groupViews = [];

    /** @var GroupViewQueryHandler */
    private $groupViewQueryHandler;

    /**
     * GroupListViewQueryHandler constructor.
     *
     * @param GroupViewQueryHandler $groupViewQueryHandler
     */
    public function __construct(GroupViewQueryHandler $groupViewQueryHandler)
    {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
    }

    /**
     * @param GroupListViewQuery $groupListViewQuery
     *
     * @return GroupView[]
     */
    public function handle(GroupListViewQuery $groupListViewQuery)
    {
        foreach ($groupListViewQuery->groups as $group) {
            $this->groupViews[] = $this->groupViewQueryHandler->handle(new GroupViewQuery($group));
        }

        return $this->groupViews;
    }
}
