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
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;

class GroupListViewQueryHandler
{
    /** @var GroupViewQueryHandler */
    private $groupViewQueryHandler;

    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /**
     * GroupListViewQueryHandler constructor.
     *
     * @param GroupViewQueryHandler    $groupViewQueryHandler
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        GroupViewQueryHandler $groupViewQueryHandler,
        GroupRepositoryInterface $groupRepository
    ) {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
        $this->groupRepository       = $groupRepository;
    }

    /**
     * @param GroupListViewQuery $groupListViewQuery
     *
     * @return GroupView[]
     */
    public function handle(GroupListViewQuery $groupListViewQuery)
    {
        $groupViews   = [];
        $sheetsGroups = $this->groupRepository->getAllByEventOrderedByTitle($groupListViewQuery->event);

        if (!empty($sheetsGroups)) {
            foreach ($sheetsGroups as $group) {
                $groupViews[] = $this->groupViewQueryHandler->handle(new GroupViewQuery($group));
            }
        }

        return $groupViews;
    }
}
