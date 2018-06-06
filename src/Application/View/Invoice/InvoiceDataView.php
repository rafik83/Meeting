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
use Proximum\Vimeet\Domain\View\OrderVatView;

class InvoiceDataView
{
    /** @var Order\SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var OrderVatView */
    public $orderVatView;

    /** @var int amount in cents */
    public $amountRemainToPay;

    /**
     * @param Order\SummaryView $summaryView
     * @param BillingInfosView  $billingInfosView
     * @param OrderVatView      $orderVatView
     * @param int               $amountRemainToPay amount in cents
     */
    public function __construct(
        Order\SummaryView $summaryView,
        BillingInfosView $billingInfosView,
        OrderVatView $orderVatView,
        int $amountRemainToPay
    ) {
        $this->summaryView = $summaryView;
        $this->billingInfosView = $billingInfosView;
        $this->orderVatView = $orderVatView;
        $this->amountRemainToPay = $amountRemainToPay;
    }
}
