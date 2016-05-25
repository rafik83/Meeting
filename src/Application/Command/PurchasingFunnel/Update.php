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
     * @var Model\Packages
     */
    public $packages;

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
        $packagesLabels               = [];
        $participantAndPlanningLabels = [];
        $optionsLabels                = [];

        foreach ($purchasingFunnel->getEvent()->getLocales() as $locale) {
            $packagesLabels[$locale]               = $purchasingFunnel->getPackagesLabel($locale);
            $participantAndPlanningLabels[$locale] = $purchasingFunnel->getParticipantAndPlanningLabel($locale);
            $optionsLabels[$locale]                = $purchasingFunnel->getOptionsLabel($locale);
        }

        $this->purchasingFunnel       = $purchasingFunnel;
        $this->title                  = $purchasingFunnel->getTitle();
        $this->packages               = new Model\Packages(
            $packagesLabels,
            $purchasingFunnel->isPackagesEnabled(),
            $purchasingFunnel->getPackages()
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
