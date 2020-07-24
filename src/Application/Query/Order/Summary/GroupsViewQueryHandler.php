<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Application\View\Order\GroupsView;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Funnel\FunnelFactory;
use Proximum\Vimeet\Domain\Package\Funnel\Step;

class GroupsViewQueryHandler
{
    /**
     * @var GroupViewQueryHandler
     */
    private $groupViewQueryHandler;

    /**
     * @var FunnelFactory
     */
    private $funnelFactory;

    /**
     * @param GroupViewQueryHandler $groupViewQueryHandler
     * @param FunnelFactory         $funnelFactory
     */
    public function __construct(
        GroupViewQueryHandler $groupViewQueryHandler,
        FunnelFactory $funnelFactory
    ) {
        $this->groupViewQueryHandler = $groupViewQueryHandler;
        $this->funnelFactory         = $funnelFactory;
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
        $funnel     = $this->funnelFactory->create($groupsViewQuery->sheet, $groupsViewQuery->locale);

        if ($order->hasType(Product::TYPE_PLAN)) {
            $groupView = $this->groupViewQueryHandler->handle(
                new GroupViewQuery(
                    $groupsViewQuery->sheet,
                    null, // no step link for type plan
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
                        $groupsViewQuery->sheet,
                        $funnel->getStepByType(Step::TYPE_PARTICIPANT_PLANNING),
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
                        $groupsViewQuery->sheet,
                        $funnel->getStepByType(Step::TYPE_PARTICIPANT_PLANNING),
                        $order,
                        $groupsViewQuery->locale,
                        Product::TYPE_PLANNING,
                        null,
                        $planView
                    )
                )
            );
        }

        $stepOption = $funnel->getStepByType(Step::TYPE_OPTIONS);

        if (empty($order->getGroupsIds())) {
            $groupView = $this->groupViewQueryHandler->handle(
                new GroupViewQuery(
                    $groupsViewQuery->sheet,
                    $stepOption,
                    $order,
                    $groupsViewQuery->locale,
                    Product::TYPE_OPTION,
                    null,
                    $planView
                )
            );

            if ($groupView->hasProductOrCustomRow()) {
                $groupsView->addGroupView($groupView);
            }
        } else {
            foreach ($order->getGroupsIds() as $groupId) {
                $groupsView->addGroupView(
                    $this->groupViewQueryHandler->handle(
                        new GroupViewQuery(
                            $groupsViewQuery->sheet,
                            $stepOption,
                            $order,
                            $groupsViewQuery->locale,
                            Product::TYPE_OPTION,
                            $groupId,
                            $planView
                        )
                    )
                );
            }
        }

        return $groupsView;
    }
}
