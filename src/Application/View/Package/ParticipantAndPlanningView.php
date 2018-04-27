<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var ProductView
     */
    public $planningView;

    /**
     * @param ParticipantsView $participantsView
     * @param ProductView      $planningView
     */
    public function __construct(ParticipantsView $participantsView, ProductView $planningView)
    {
        $this->participantsView = $participantsView;
        $this->planningView     = $planningView;
    }
}
