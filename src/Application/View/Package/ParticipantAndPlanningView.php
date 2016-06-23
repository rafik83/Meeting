<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class ParticipantAndPlanningView extends AbstractProductsView
{
    /**
     * @var ParticipantsView
     */
    public $participantsView;

    /**
     * @var PlanningView
     */
    public $planningView;

    /**
     * @param ParticipantsView $participantsView
     * @param PlanningView     $planningView
     */
    public function __construct(ParticipantsView $participantsView, PlanningView $planningView)
    {
        $this->participantsView = $participantsView;
        $this->planningView     = $planningView;
    }
}
