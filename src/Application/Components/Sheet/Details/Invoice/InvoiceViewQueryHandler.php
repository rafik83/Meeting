<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Details\Invoice;

use Proximum\Vimeet\Application\View\Sheet\Details\Invoice\InvoiceView;
use Proximum\Vimeet\Application\View\Sheet\Details\Invoice\OrderView;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;

class InvoiceViewQueryHandler
{
    /** @var InvoiceRepositoryInterface */
    private $invoiceRepository;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param EventUrlGeneratorInterface $eventUrlGenerator
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    /**
     * @param InvoiceViewQuery $invoiceViewQuery
     *
     * @return InvoiceView[]
     */
    public function handle(InvoiceViewQuery $invoiceViewQuery)
    {
        $invoiceViews = [];

        $invoices = $this->invoiceRepository->findBySheet($invoiceViewQuery->sheet);

        foreach ($invoices as $invoice) {
            $orderViews = array_map(function (Order $order) {
                return new OrderView($order->getId(), $order->getNumero());
            }, $invoice->getOrders());

            $url = $this->eventUrlGenerator->generateEventAbsoluteUrl(
                $invoiceViewQuery->sheet->getEvent(),
                'event_invoice_show',
                [
                    'sheet'   => $invoiceViewQuery->sheet->getId(),
                    'invoice' => $invoice->getId(),
                    'hash'    => $invoice->getHash(),
                    'format'  => 'pdf',
                    '_locale' => $invoice->getEvent()->getAvailableLocale($invoiceViewQuery->sheet->getOwnerLocale()),
                ]
            );

            $invoiceViews[] = new InvoiceView(
                $invoice->getId(),
                $invoice->getNumber(),
                $invoice->getTotal(),
                $invoice->getTotalWithVat(),
                $invoice->getCurrency(),
                $invoice->getCreatedAt(),
                $orderViews,
                $url
            );
        }

        return $invoiceViews;
    }
}
