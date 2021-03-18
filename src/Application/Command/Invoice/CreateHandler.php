<?php

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Application\Components\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Invoice\InvoiceNumberGenerator;

class CreateHandler
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var OrdersToInvoice
     */
    private $ordersToInvoice;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreateHandler constructor.
     *
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrderRepositoryInterface   $orderRepository
     * @param OrdersToInvoice            $ordersToInvoice
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        InvoiceRepositoryInterface $invoiceRepository,
        OrderRepositoryInterface $orderRepository,
        OrdersToInvoice $ordersToInvoice,
        \DateTimeInterface $dateTime
    ) {
        $this->invoiceRepository = $invoiceRepository;
        $this->dateTime          = $dateTime;
        $this->ordersToInvoice   = $ordersToInvoice;
        $this->orderRepository   = $orderRepository;
    }

    /**
     * @param Create $create
     *
     * @return Invoice[]
     */
    public function handle(Create $create)
    {
        // do not process disabled sheets
        if (false === $create->sheet->isEnabled()) {
            return [];
        }

        $ordersToInvoiceView = $this->ordersToInvoice->getOrdersToInvoiceViewForSheet($create->sheet);

        if (null === $ordersToInvoiceView) {
            return [];
        }

        $lastInvoiceForSheet = $this->invoiceRepository->getLastInvoiceForEventPrefix(
            $create->prefix,
            $this->dateTime->format('Y')
        );

        $invoiceIncrement = InvoiceNumberGenerator::generate($lastInvoiceForSheet);

        $invoice = new Invoice(
            $create->sheet->getEvent(),
            $create->sheet,
            $create->prefix,
            $create->prefix->getPrefix(),
            $this->dateTime->format('Y'),
            $invoiceIncrement,
            $ordersToInvoiceView->isVatApplicable(),
            $ordersToInvoiceView->getVatMode(),
            $ordersToInvoiceView->getVatRate(),
            $ordersToInvoiceView->getTotal(),
            $ordersToInvoiceView->getTotalWithVat(),
            $ordersToInvoiceView->getVatAmount(),
            $ordersToInvoiceView->getCurrency(),
            $ordersToInvoiceView->getData(),
            $this->dateTime
        );

        $this->invoiceRepository->add($invoice);

        // Flag Order with generated Invoice
        foreach ($ordersToInvoiceView->getOrders() as $order) {
            $order->setInvoice($invoice);
            $this->orderRepository->set($order);
        }

        return [$invoice];
    }
}
