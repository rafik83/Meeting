<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Invoice\Create;
use Proximum\Vimeet\Application\Command\Invoice\CreateHandler;
use Proximum\Vimeet\Domain\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Invoice\InvoiceNumberGenerator;
use Proximum\Vimeet\Domain\View\Invoice\OrdersToInvoiceView;

class BatchGenerateInvoiceHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @var OrdersToInvoice
     */
    private $ordersToInvoice;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchGenerateInvoiceHandler constructor.
     *
     * @param SheetRepositoryInterface      $sheetRepository
     * @param InvoiceRepositoryInterface    $invoiceRepository
     * @param OrdersToInvoice               $ordersToInvoice
     * @param OrderRepositoryInterface      $orderRepository
     * @param \DateTimeInterface            $dateTime
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrdersToInvoice            $ordersToInvoice,
        OrderRepositoryInterface   $orderRepository,
        \DateTimeInterface         $dateTime
    )
    {
        $this->sheetRepository   = $sheetRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->ordersToInvoice   = $ordersToInvoice;
        $this->orderRepository   = $orderRepository;
        $this->datetime          = $dateTime;
    }

    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        $sheets         = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);
        $createHandler  = new CreateHandler($this->invoiceRepository);
        $invoiceGeneratedCounter = 0;

        foreach ($sheets as $sheet) {

            $ordersToInvoiceView = $this->ordersToInvoice->getOrdersToInvoiceViewForSheet($sheet);

            if ($ordersToInvoiceView instanceof OrdersToInvoiceView) {

                $invoiceNumberGenerator = new InvoiceNumberGenerator();

                $create = new Create(
                    $sheet->getEvent(),
                    $sheet,
                    $sheet->getEvent()->getInvoicePrefix(),
                    $sheet->getEvent()->getInvoicePrefix()->getPrefix(),
                    $this->datetime->format('Y'),
                    $invoiceNumberGenerator->generate(),
                    $ordersToInvoiceView->getTotal(),
                    $ordersToInvoiceView->getTotalWithVat(),
                    $ordersToInvoiceView->getVatAmount(),
                    $this->datetime
                );

                $invoice = $createHandler->handle($create);
                
                // Flag Order with generated Invoice
                foreach($ordersToInvoiceView->getOrders() as $order) {
                    $order->setInvoice($invoice);
                    $this->orderRepository->set($order);
                }
                
                $invoiceGeneratedCounter++;
            }
        }

        return new BatchResult($invoiceGeneratedCounter, $batchGenerateInvoice->getMessage() . 'generateInvoice.success');
    }
}
