<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Repository\Invoice\InvoiceRepositoryInterface;
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
     * CreateHandler constructor.
     *
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository, \DateTimeInterface $dateTime)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->dateTime          = $dateTime;
    }
    
    /**
     * @param Create $create
     *
     * @return Invoice
     */
    public function handle(Create $create)
    {
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
            $create->total,
            $create->totalWithVat,
            $create->vatAmount,
            $this->dateTime
        );
        
        $this->invoiceRepository->add($invoice);
        
        return $invoice;
    }
}
