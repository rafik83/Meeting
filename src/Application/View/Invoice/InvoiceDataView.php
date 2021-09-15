<?php

namespace Proximum\Vimeet\Application\View\Invoice;

use Proximum\Vimeet\Application\View\Order;
use Proximum\Vimeet\Application\View\Package\Vat\VatListView;

class InvoiceDataView
{
    /** @var Order\SummaryView */
    public $summaryView;

    /** @var BillingInfosView */
    public $billingInfosView;

    /** @var VatListView */
    public $vatListView;

    /** @var int amount in cents */
    public $amountRemainToPay;

    /**
     * @param Order\SummaryView $summaryView
     * @param BillingInfosView  $billingInfosView
     * @param VatListView       $vatListView
     * @param int               $amountRemainToPay amount in cents
     */
    public function __construct(
        Order\SummaryView $summaryView,
        BillingInfosView $billingInfosView,
        VatListView $vatListView,
        int $amountRemainToPay
    ) {
        $this->summaryView = $summaryView;
        $this->billingInfosView = $billingInfosView;
        $this->vatListView = $vatListView;
        $this->amountRemainToPay = $amountRemainToPay;
    }
}
