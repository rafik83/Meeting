<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class GroupsView
{
    /**
     * @var PlanGroupView
     */
    public $planGroup;

    /**
     * @var ParticipantGroupView
     */
    public $participantGroup;

    /**
     * @var PlanningGroupView
     */
    public $planningGroup;

    /**
     * @var GroupView[]
     */
    public $groups = [];

    /**
     * @param PlanGroupView        $planGroup
     * @param ParticipantGroupView $participantGroup
     * @param PlanningGroupView    $planningGroup
     * @param GroupView[]          $groups
     */
    public function __construct($planGroup, $participantGroup, $planningGroup, array $groups)
    {
        $this->planGroup        = $planGroup;
        $this->participantGroup = $participantGroup;
        $this->planningGroup    = $planningGroup;
        $this->groups           = $groups;
    }

    public function getTotal()
    {
        $total = 0;

        if (null !== $this->planGroup) {
            $total += $this->planGroup->total;
        }

        if (null !== $this->participantGroup) {
            $total += $this->participantGroup->total;
        }

        if (null !== $this->planningGroup) {
            $total += $this->planningGroup->total;
        }

        if (!empty($this->groups)) {
            foreach ($this->groups as $group) {
                $total += $group->total;
            }
        }

        return $total;
    }
}
