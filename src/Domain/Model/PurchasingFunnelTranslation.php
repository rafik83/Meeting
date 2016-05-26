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
     * @var int
     */
    private $id;

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
    private $plansLabel;

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
     * @param string           $plansLabel
     * @param string           $participantAndPlanningLabel
     * @param string           $optionsLabel
     */
    public function __construct(PurchasingFunnel $purchasingFunnel, $locale, $plansLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->purchasingFunnel            = $purchasingFunnel;
        $this->locale                      = $locale;
        $this->plansLabel               = $plansLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $plansLabel
     * @param string $participantAndPlanningLabel
     * @param string $optionsLabel
     */
    public function set($plansLabel, $participantAndPlanningLabel, $optionsLabel)
    {
        $this->plansLabel                  = $plansLabel;
        $this->participantAndPlanningLabel = $participantAndPlanningLabel;
        $this->optionsLabel                = $optionsLabel;
    }

    /**
     * Get plansLabel
     *
     * @return string
     */
    public function getPlansLabel()
    {
        return $this->plansLabel;
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
