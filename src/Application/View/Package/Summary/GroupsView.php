<?php

namespace Proximum\Vimeet\Application\View\Package\Summary;

class GroupsView
{
    /**
     * @var null|PlanGroupView
     */
    public $planGroup;

    /**
     * @var null|ParticipantAndPlanningGroupView
     */
    public $participantAndPlanningGroup;

    /**
     * @var GroupView[]
     */
    public $groups = [];

    /**
     * @param PlanGroupView|null                   $planGroup
     * @param ParticipantAndPlanningGroupView|null $participantAndPlanningGroup
     * @param GroupView[]                          $groups
     */
    public function __construct(
        PlanGroupView $planGroup = null,
        ParticipantAndPlanningGroupView $participantAndPlanningGroup = null,
        array $groups = []
    ) {
        $this->planGroup = $planGroup;
        $this->participantAndPlanningGroup = $participantAndPlanningGroup;
        $this->groups = $groups;
    }

    /**
     * @return float|int
     */
    public function getTotal()
    {
        $total = 0;

        if (null !== $this->planGroup) {
            $total += $this->planGroup->total;
        }

        if (null !== $this->participantAndPlanningGroup) {
            $total += $this->participantAndPlanningGroup->total;
        }

        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $total += $group->total;
            }
        }

        return $total;
    }
}
