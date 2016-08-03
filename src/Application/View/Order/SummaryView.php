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
     * @param float              $totalWithoutVat
     * @param float              $vatAmount
     * @param float              $totalWithVat
     * @param string             $currency
     */
    public function __construct(
        GroupsView $groups,
        PromotionCodesView $promotionCodes,
        $vatApplicable,
        $vatRate,
        $vatMode,
        $totalWithoutVat,
        $vatAmount,
        $totalWithVat,
        $currency
    ) {
        $this->groups          = $groups;
        $this->promotionCodes  = $promotionCodes;
        $this->vatApplicable   = $vatApplicable;
        $this->vatRate         = $vatRate;
        $this->vatMode         = $vatMode;
        $this->totalWithoutVat = $totalWithoutVat;
        $this->vatAmount       = $vatAmount;
        $this->totalWithVat    = $totalWithVat;
        $this->currency        = $currency;
    }
}
