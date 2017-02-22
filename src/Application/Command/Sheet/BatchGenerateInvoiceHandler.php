<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

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
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchGenerateInvoiceHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \DateTimeInterface $dateTime
     */
    public function __construct(
        SheetRepositoryInterface   $sheetRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        \DateTimeInterface         $dateTime
    )
    {
        $this->sheetRepository   = $sheetRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->datetime          = $dateTime;
    }

    public function handle(BatchGenerateInvoice $batchGenerateInvoice)
    {
        // Get sheets
        $sheets = $this->sheetRepository->getSheetsById($batchGenerateInvoice->ids);
        $generatedInvoice = [];

        foreach ($sheets as $sheet) {
            if ($sheet->hasOrders()) {

                foreach ($sheet->getOrders() as $order) {
                    if (!$order->isCancelled() && $order->getTotal() > 0) {
                        $generatedInvoice[] = $order;
                    }
                }
            }
        }
        return new BatchResult(count($generatedInvoice), $batchGenerateInvoice->getMessage() . 'generateInvoice.success');
    }
}
