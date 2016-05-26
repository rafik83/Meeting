<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel;

use Proximum\Vimeet\Domain\Model\PurchasingFunnel;

class Update
{
    /**
     * @var PurchasingFunnel
     */
    public $purchasingFunnel;

    /**
     * @var string
     */
    public $title;

    /**
     * @var Model\Plans
     */
    public $plans;

    /**
     * @var Model\ParticipantAndPlanning
     */
    public $participantAndPlanning;

    /**
     * @var Model\Options
     */
    public $options;

    /**
     * Update constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     */
    public function __construct(PurchasingFunnel $purchasingFunnel)
    {
        $plansLabels               = [];
        $participantAndPlanningLabels = [];
        $optionsLabels                = [];

        foreach ($purchasingFunnel->getEvent()->getLocales() as $locale) {
            $plansLabels[$locale]               = $purchasingFunnel->getPlansLabel($locale);
            $participantAndPlanningLabels[$locale] = $purchasingFunnel->getParticipantAndPlanningLabel($locale);
            $optionsLabels[$locale]                = $purchasingFunnel->getOptionsLabel($locale);
        }

        $this->purchasingFunnel       = $purchasingFunnel;
        $this->title                  = $purchasingFunnel->getTitle();
        $this->plans               = new Model\Plans(
            $plansLabels,
            $purchasingFunnel->isPlansEnabled(),
            $purchasingFunnel->getPlans()
        );
        $this->participantAndPlanning = new Model\ParticipantAndPlanning(
            $participantAndPlanningLabels,
            $purchasingFunnel->isParticipantAndPlanningEnabled(),
            $purchasingFunnel->getParticipant(),
            $purchasingFunnel->getPlanning()
        );
        $this->options                = new Model\Options(
            $optionsLabels,
            $purchasingFunnel->isOptionsEnabled(),
            $purchasingFunnel->getOptions()
        );
    }
}
