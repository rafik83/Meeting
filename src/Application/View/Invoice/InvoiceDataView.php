<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

use Proximum\Vimeet\Application\View\Order\SummaryView;
use Proximum\Vimeet\Application\View\Sheet\BillingInfos\BillingInfosView;

class InvoiceDataView
{
    /** @var  SummaryView */
    public $summaryView;
    
    /** @var  BillingInfosView */
    public $billingInfosView;
    
    /** @var float */
    public $amountRemainToPay;
    
    /**
     * InvoiceDataView constructor.
     *
     * @param SummaryView       $summaryView
     * @param BillingInfosView  $billingInfosView
     * @param float             $amountRemainToPay
     */
    public function __construct(SummaryView $summaryView, BillingInfosView $billingInfosView, $amountRemainToPay)
    {
        $this->summaryView       = $summaryView;
        $this->billingInfosView  = $billingInfosView;
        $this->amountRemainToPay = $amountRemainToPay;
    }
}
