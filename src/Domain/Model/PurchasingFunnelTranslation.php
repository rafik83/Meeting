<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PurchasingFunnelTranslation
{
    /**
     * @var PurchasingFunnel
     */
    private $purchasingFunnel;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $packagesLabel;

    /**
     * @var string
     */
    private $participantAndPlanningLabel;

    /**
     * @var string
     */
    private $optionsLabel;

    /**
     * PurchasingFunnelTranslation constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     * @param string           $locale
     * @param string           $packagesLabel
     * @param string           $participantAndPlanningLabel
     * @param string           $optionsLabel
     */
    public function __construct(PurchasingFunnel $purchasingFunnel, $locale, $packagesLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->purchasingFunnel            = $purchasingFunnel;
        $this->locale                      = $locale;
        $this->packagesLabel               = $packagesLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * @param string $packagesLabel
     * @param string $participantAndPlanningLabel
     * @param string $optionsLabel
     */
    public function set($packagesLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->packagesLabel               = $packagesLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * Get packagesLabel
     *
     * @return string
     */
    public function getPackagesLabel()
    {
        return $this->packagesLabel;
    }

    /**
     * Get participantAndPlanningLabel
     *
     * @return string
     */
    public function getParticipantAndPlanningLabel()
    {
        return $this->participantAndPlanningLabel;
    }

    /**
     * Get optionsLabel
     *
     * @return string
     */
    public function getOptionsLabel()
    {
        return $this->optionsLabel;
    }
}
