<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Model\PackageGroup;

class GroupsViewQueryHandler
{
    /**
     * @var GroupViewQueryHandler
     */
    private $groupViewQueryHandler;

    /**
     * @var PlanGroupViewQueryHandler
     */
    private $planGroupViewQueryHandler;

    /**
     * @var ParticipantGroupViewQueryHandler
     */
    private $participantGroupViewQueryHandler;

    /**
     * @var PlanningGroupViewQueryHandler
     */
    private $planningGroupViewQueryHandler;

    /**
     * @param PlanGroupViewQueryHandler        $planGroupViewQueryHandler
     * @param ParticipantGroupViewQueryHandler $participantGroupViewQueryHandler
     * @param PlanningGroupViewQueryHandler    $planningGroupViewQueryHandler
     * @param GroupViewQueryHandler            $groupViewQueryHandler
     */
    public function __construct(
        PlanGroupViewQueryHandler $planGroupViewQueryHandler,
        ParticipantGroupViewQueryHandler $participantGroupViewQueryHandler,
        PlanningGroupViewQueryHandler $planningGroupViewQueryHandler,
        GroupViewQueryHandler $groupViewQueryHandler
    ) {
        $this->planGroupViewQueryHandler        = $planGroupViewQueryHandler;
        $this->participantGroupViewQueryHandler = $participantGroupViewQueryHandler;
        $this->planningGroupViewQueryHandler    = $planningGroupViewQueryHandler;
        $this->groupViewQueryHandler            = $groupViewQueryHandler;
    }

    /**
     * @param GroupsViewQuery $groupsViewQuery
     *
     * @return GroupsView
     */
    public function handle(GroupsViewQuery $groupsViewQuery)
    {
        $groupViewQueryHandler = $this->groupViewQueryHandler;
        $cart                  = $groupsViewQuery->cart;
        $package               = $groupsViewQuery->sheet->getPackage();
        $planGroupView         = null;
        $participantGroupView  = null;
        $planningGroupView     = null;
        $groupsView            = [];

        if ($package->isPlansEnabled() ) {
            $planGroupView = $this->planGroupViewQueryHandler->handle(
                new PlanGroupViewQuery(
                    $groupsViewQuery->sheet,
                    $cart,
                    $groupsViewQuery->locale
                )
            );
        }

        if ($package->isParticipantAndPlanningEnabled() && null !== $cart->getParticipantRow()) {
            $participantGroupView = $this->participantGroupViewQueryHandler->handle(
                new ParticipantGroupViewQuery(
                    $groupsViewQuery->sheet,
                    $cart,
                    $groupsViewQuery->locale,
                    $planGroupView
                )
            );
        }

        if ($package->isParticipantAndPlanningEnabled() && null !== $cart->getPlanningRow()) {
            $planningGroupView = $this->planningGroupViewQueryHandler->handle(
                new PlanningGroupViewQuery(
                    $groupsViewQuery->sheet,
                    $cart,
                    $groupsViewQuery->locale,
                    $planGroupView
                )
            );
        }

        if ($package->isOptionsEnabled() && null !== $cart->getOptionsRow()) {
            $groupsView = array_map(
                function (PackageGroup $group) use ($groupViewQueryHandler, $groupsViewQuery, $planGroupView) {
                    return $groupViewQueryHandler->handle(
                        new GroupViewQuery(
                            $groupsViewQuery->sheet,
                            $group,
                            $groupsViewQuery->cart,
                            $groupsViewQuery->locale,
                            $planGroupView
                        )
                    );
                }, $groupsViewQuery->sheet->getType()->getPackage()->getGroups()
            );
        }

        return new GroupsView(
            $planGroupView,
            $participantGroupView,
            $planningGroupView,
            $groupsView
        );
    }
}
