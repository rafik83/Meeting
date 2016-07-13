<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

use Proximum\Vimeet\Domain\Model\Event;

class SummaryView
{
    /**
     * @var GroupsView
     */
    public $groups;

    /**
     * @var PromotionCodesView
     */
    public $promotionCodes;

    /**
     * @var float
     */
    public $total;

    /**
     * @var float
     */
    public $vat;

    /**
     * @var float
     */
    public $vatRate;

    /**
     * @var float
     */
    public $totalPlusVat;

    /**
     * @var bool
     */
    public $vatApplicable;

    /**
     * @var string
     */
    public $totalVatMode;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param GroupsView         $groups
     * @param PromotionCodesView $promotionCodes
     * @param bool               $vatApplicable
     * @param float              $vatRate
     * @param string             $vatMode
     * @param string             $currency
     */
    public function __construct(
        GroupsView $groups,
        PromotionCodesView $promotionCodes,
        $vatApplicable,
        $vatRate,
        $vatMode,
        $currency
    ) {
        $this->groups         = $groups;
        $this->promotionCodes = $promotionCodes;
        $this->total          = $groups->getTotal() + $promotionCodes->getTotal();
        $this->vatApplicable  = $vatApplicable;
        $this->vatRate        = $vatRate;
        $this->totalPlusVat   = $this->total;
        $this->vatMode        = $vatMode;
        $this->currency       = $currency;

        if ($vatApplicable) {
            $this->vat          = ($this->total * $vatRate) / 100;
            $this->totalPlusVat = $this->vat + $this->total;
            $this->totalVatMode = Event::VAT_MODE_ATI;
        }
    }
}
