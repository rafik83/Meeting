<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;


use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

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
     * @var string
     */
    public $vatMode;

    /**
     * @var float
     */
    public $vat;

    /**
     * @var float
     */
    public $total;

    /**
     * @var float
     */
    public $totalVat = 0;

    /**
     * @var float
     */
    public $totalPlusVat;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var Funnel
     */
    public $funnel;

    /**
     * @var bool
     */
    public $mustPayVat;

    /**
     * @param Funnel             $funnel
     * @param GroupsView         $groupsView
     * @param PromotionCodesView $promotionCodesView
     * @param string             $vatMode
     * @param float              $vat
     * @param float              $total
     * @param string             $currency
     * @param bool               $mustPayVat
     */
    public function __construct(
        Funnel $funnel,
        GroupsView $groupsView,
        PromotionCodesView $promotionCodesView,
        $vatMode,
        $vat,
        $total,
        $currency,
        $mustPayVat
    ) {
        $this->funnel     = $funnel;
        $this->groups     = $groupsView;
        $this->vatMode    = $vatMode;
        $this->vat        = $vat;
        $this->total      = $total;
        $this->mustPayVat = $mustPayVat;
        $this->currency   = $currency;

        if ($mustPayVat) {
            $this->totalVat     = ($total * $vat) / 100;
            $this->totalPlusVat = $total + $this->totalVat;
        } else {
            $this->totalVat     = 0;
            $this->totalPlusVat = $total;
        }

        $this->promotionCodes = $promotionCodesView;
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return \count($this->funnel->getCart()->getRows()) === 0;
    }
}
