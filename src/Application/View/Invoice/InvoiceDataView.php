<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

use Proximum\Vimeet\Application\View\Order;

class InvoiceDataView
{
    /** @var Order\SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var float */
    public $amountRemainToPay;

    /**
     * @param Order\SummaryView $summaryView
     * @param BillingInfosView  $billingInfosView
     * @param float             $amountRemainToPay
     */
    public function __construct(Order\SummaryView $summaryView, BillingInfosView $billingInfosView, $amountRemainToPay)
    {
        $this->summaryView       = $summaryView;
        $this->billingInfosView  = $billingInfosView;
        $this->amountRemainToPay = $amountRemainToPay;
    }
}
