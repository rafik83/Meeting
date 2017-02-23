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
use Proximum\Vimeet\Domain\Order\OrdersToInvoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Invoice\InvoiceNumberGenerator;

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
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchGenerateInvoiceHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param OrdersToInvoice $ordersToInvoice
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        OrdersToInvoice            $ordersToInvoice,
        \DateTimeInterface         $dateTime
    )
    {
        $this->sheetRepository   = $sheetRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->ordersToInvoice   = $ordersToInvoice;
        $this->datetime          = $dateTime;
    }

    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);
        $invoiceGeneratedCounter = 0;

        foreach ($sheets as $sheet) {

            $ordersToInvoiceView    = $this->ordersToInvoice->getOrdersToInvoiceViewForSheet($sheet);
            $invoiceNumberGenerator = new InvoiceNumberGenerator($sheet->getEvent());
            $create                 = new Create();
            $create->event          = $sheet->getEvent();
            $create->sheet          = $sheet;
            $create->prefix         = $sheet->getEvent()->getInvoicePrefix();
            $create->total          = $ordersToInvoiceView->getTotal();
            $create->totalWithVat   = $ordersToInvoiceView->getTotalWithVat();
            $create->vatAmount      = $ordersToInvoiceView->getVatAmount();
            $create->createdAt      = $this->datetime;
            $create->invoicePrefix  = $create->prefix->getPrefix();
            $create->invoiceYear    = $this->datetime->format('Y');
            $create->invoiceNumber  = $invoiceNumberGenerator->generate();
            $invoiceGeneratedCounter++;
        }
        return new BatchResult($invoiceGeneratedCounter, $batchGenerateInvoice->getMessage() . 'generateInvoice.success');
    }
}
