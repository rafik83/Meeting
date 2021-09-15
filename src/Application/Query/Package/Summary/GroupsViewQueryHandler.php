<?php

namespace Proximum\Vimeet\Application\Query\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Summary\GroupsView;
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
     * @var ParticipantAndPlanningGroupViewQueryHandler
     */
    private $participantAndPlanningGroupViewQueryHandler;

    /**
     * @param PlanGroupViewQueryHandler                   $planGroupViewQueryHandler
     * @param ParticipantAndPlanningGroupViewQueryHandler $participantAndPlanningGroupViewQueryHandler
     * @param GroupViewQueryHandler                       $groupViewQueryHandler
     */
    public function __construct(
        PlanGroupViewQueryHandler $planGroupViewQueryHandler,
        ParticipantAndPlanningGroupViewQueryHandler $participantAndPlanningGroupViewQueryHandler,
        GroupViewQueryHandler $groupViewQueryHandler
    ) {
        $this->planGroupViewQueryHandler = $planGroupViewQueryHandler;
        $this->participantAndPlanningGroupViewQueryHandler = $participantAndPlanningGroupViewQueryHandler;
        $this->groupViewQueryHandler = $groupViewQueryHandler;
    }

    /**
     * @param GroupsViewQuery $groupsViewQuery
     *
     * @return GroupsView
     */
    public function handle(GroupsViewQuery $groupsViewQuery): GroupsView
    {
        $groupViewQueryHandler = $this->groupViewQueryHandler;
        $cart = $groupsViewQuery->cart;
        $package = $groupsViewQuery->sheet->getPackage();
        $planGroupView = null;
        $participantAndPlanningGroupView = null;
        $groupsView = [];

        if ($package->isPlansEnabled()) {
            $planGroupView = $this->planGroupViewQueryHandler->handle(
                new PlanGroupViewQuery(
                    $groupsViewQuery->sheet,
                    $cart,
                    $groupsViewQuery->locale
                )
            );
        }

        if ($package->isParticipantAndPlanningEnabled()
            && (\count($cart->getParticipantRows()) || null !== $cart->getPlanningRow())
        ) {
            $participantAndPlanningGroupView = $this->participantAndPlanningGroupViewQueryHandler->handle(
                new ParticipantAndPlanningGroupViewQuery(
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
            $participantAndPlanningGroupView,
            $groupsView
        );
    }
}
