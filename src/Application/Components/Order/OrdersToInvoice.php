<?php

namespace Proximum\Vimeet\Application\Components\Order;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQuery;
use Proximum\Vimeet\Application\Query\Invoice\InvoiceDataQueryHandler;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Order\Merger;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class OrdersToInvoice
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var Merger */
    private $orderMerger;

    /** @var InvoiceDataQueryHandler */
    private $invoiceDataQueryHandler;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        Merger $orderMerger,
        InvoiceDataQueryHandler $invoiceDataQueryHandler,
        SerializerAdapterInterface $serializerAdapter
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderMerger = $orderMerger;
        $this->invoiceDataQueryHandler = $invoiceDataQueryHandler;
        $this->serializerAdapter = $serializerAdapter;
    }

    /**
     * @throws MissingBillingInfoException
     */
    public function getOrdersToInvoiceViewForSheet(Sheet $sheet): ?OrdersToInvoiceView
    {
        $orders = $this->orderRepository->findNotCancelledAndNotInvoicedBySheet($sheet);

        if (0 === \count($orders)) {
            return null;
        }

        $orderMerged = $this->orderMerger->merge($orders);

        if ($orderMerged->getTotalWithoutVat() <= 0) {
            return null;
        }

        $invoiceDataView = $this->invoiceDataQueryHandler->handle(
            new InvoiceDataQuery(
                $orderMerged->getSheet(),
                $orderMerged,
                $orderMerged->getSheet()->getEvent()->getFallback()
            )
        );

        $data = $this->serializerAdapter->serialize($invoiceDataView, 'json');

        $event = $sheet->getEvent();

        return new OrdersToInvoiceView(
            $orders,
            $data,
            $invoiceDataView->vatListView->vatApplicable,
            $event->getMode(),
            $event->getVat(),
            $invoiceDataView->vatListView->total,
            $invoiceDataView->vatListView->getVatAmount(),
            $invoiceDataView->vatListView->totalWithVat,
            $orderMerged->getCurrency()
        );
    }
}
