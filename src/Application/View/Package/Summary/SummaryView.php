<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

use Proximum\Vimeet\Application\View\Package\Vat\VatListView;
use Proximum\Vimeet\Domain\Package\Funnel\Funnel;

class SummaryView
{
    /** @var GroupsView */
    public $groups;

    /** @var PromotionCodesView */
    public $promotionCodes;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $total;

    /** @var float */
    public $totalPlusVat;

    /** @var string */
    public $currency;

    /** @var Funnel */
    public $funnel;

    /** @var bool */
    public $mustPayVat;

    /** @var VatListView */
    public $vatListView;

    /**
     * @param Funnel             $funnel
     * @param GroupsView         $groupsView
     * @param PromotionCodesView $promotionCodesView
     * @param string             $vatMode
     * @param float              $total
     * @param float              $totalPlusVat
     * @param string             $currency
     * @param bool               $mustPayVat
     * @param VatListView        $vatListView
     */
    public function __construct(
        Funnel $funnel,
        GroupsView $groupsView,
        PromotionCodesView $promotionCodesView,
        $vatMode,
        float $total,
        float $totalPlusVat,
        $currency,
        $mustPayVat,
        VatListView $vatListView
    ) {
        $this->funnel = $funnel;
        $this->groups = $groupsView;
        $this->vatMode = $vatMode;
        $this->total = $total;
        $this->mustPayVat = $mustPayVat;
        $this->currency = $currency;
        $this->promotionCodes = $promotionCodesView;
        $this->totalPlusVat = $totalPlusVat;
        $this->vatListView = $vatListView;
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === \count($this->funnel->getCart()->getRows());
    }
}
