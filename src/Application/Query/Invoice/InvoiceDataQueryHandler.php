<?php

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQuery;
use Proximum\Vimeet\Application\Query\Invoice\BillingInfos\BillingInfosQueryHandler;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQuery;
use Proximum\Vimeet\Application\Query\Order\OrderVat\OrderVatViewQueryHandler;
use Proximum\Vimeet\Application\Query\Order\SummaryQuery;
use Proximum\Vimeet\Application\Query\Order\SummaryQueryHandler;
use Proximum\Vimeet\Application\View\Invoice\InvoiceDataView;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;

class InvoiceDataQueryHandler
{
    /** @var BillingInfosQueryHandler */
    public $billingInfosQueryHandler;

    /** @var SummaryQueryHandler */
    public $summaryQueryHandler;

    /** @var Balance */
    public $balance;

    /** @var OrderVatViewQueryHandler */
    private $orderVatViewQueryHandler;

    public function __construct(
        BillingInfosQueryHandler $billingInfosQueryHandler,
        SummaryQueryHandler $summaryQueryHandler,
        Balance $balance,
        OrderVatViewQueryHandler $orderVatViewQueryHandler
    ) {
        $this->billingInfosQueryHandler = $billingInfosQueryHandler;
        $this->summaryQueryHandler = $summaryQueryHandler;
        $this->balance = $balance;
        $this->orderVatViewQueryHandler = $orderVatViewQueryHandler;
    }

    /**
     * @throws MissingBillingInfoException
     */
    public function handle(InvoiceDataQuery $invoiceDataQuery): InvoiceDataView
    {
        return new InvoiceDataView(
            $this->summaryQueryHandler->handle(
                new SummaryQuery($invoiceDataQuery->sheet, $invoiceDataQuery->order, $invoiceDataQuery->locale)
            ),
            $this->billingInfosQueryHandler->handle(new BillingInfosQuery($invoiceDataQuery->sheet)),
            $this->orderVatViewQueryHandler->handle(new OrderVatViewQuery($invoiceDataQuery->order))->vatListView,
            $this->balance->getRemainingToPay($invoiceDataQuery->sheet)
        );
    }
}
