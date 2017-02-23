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

class CreateHandler
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * CreateHandler constructor.
     *
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $this->invoiceRepository->add(new Invoice(
            $create->event,
            $create->sheet,
            $create->prefix,
            $create->invoicePrefix,
            $create->invoiceYear,
            $create->invoiceNumber,
            $create->total,
            $create->totalWithVat,
            $create->vatAmount,
            $create->createdAt
        ));
    }
}
