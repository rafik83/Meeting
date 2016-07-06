<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\GroupsView;
use Proximum\Vimeet\Domain\Model\Product;

class GroupsViewQueryHandler
{
    /**
     * @var GroupViewQueryHandler
     */
    private $groupViewQueryHandler;

    /**
     * @param GroupViewQueryHandler $groupViewQueryHandler
     */
    public function __construct(GroupViewQueryHandler $groupViewQueryHandler)
    {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
    }

    /**
     * @param GroupsViewQuery $groupsViewQuery
     *
     * @return GroupsView
     */
    public function handle(GroupsViewQuery $groupsViewQuery)
    {
        $order      = $groupsViewQuery->order;
        $groupsView = new GroupsView();
        $planView   = null;

        if ($order->hasType(Product::TYPE_PLAN)) {
            $groupView = $this->groupViewQueryHandler->handle(
                new GroupViewQuery(
                    $order,
                    $groupsViewQuery->locale,
                    Product::TYPE_PLAN
                )
            );

            $groupsView->addGroupView($groupView);

            $planView = reset($groupView->products);

            if (false === $planView) {
                $planView = null;
            }
        }

        if ($order->hasType(Product::TYPE_PARTICIPANT)) {
            $groupsView->addGroupView(
                $this->groupViewQueryHandler->handle(
                    new GroupViewQuery(
                        $order,
                        $groupsViewQuery->locale,
                        Product::TYPE_PARTICIPANT,
                        null,
                        $planView
                    )
                )
            );
        }

        if ($order->hasType(Product::TYPE_PLANNING)) {
            $groupsView->addGroupView(
                $this->groupViewQueryHandler->handle(
                    new GroupViewQuery(
                        $order,
                        $groupsViewQuery->locale,
                        Product::TYPE_PLANNING,
                        null,
                        $planView
                    )
                )
            );
        }

        foreach ($order->getGroupsIds() as $groupId) {
            $groupsView->addGroupView(
                $this->groupViewQueryHandler->handle(
                    new GroupViewQuery(
                        $order,
                        $groupsViewQuery->locale,
                        Product::TYPE_OPTION,
                        $groupId,
                        $planView
                    )
                )
            );
        }

        return $groupsView;
    }
}
