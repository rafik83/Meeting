<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory;

use Proximum\Vimeet\Domain\Exception\Invoice\CanNotExplodeNotValidNumeroInvoiceException;
use Proximum\Vimeet\Domain\Invoice\Numero\Exploder;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;

class InvoiceFactory
{
    /**
     * @param string     $numero
     * @param Sheet|null $sheet
     *
     * @throws CanNotExplodeNotValidNumeroInvoiceException
     *
     * @return Invoice
     */
    public static function create($numero, Sheet $sheet = null): Invoice
    {
        if (null === $sheet) {
            $sheet = SheetFactory::create();
        }

        $numeroView = Exploder::explode($numero);

        $invoice = new Invoice(
            $sheet->getEvent(),
            $sheet,
            $sheet->getEvent()->getInvoicePrefix(),
            $numeroView->prefix,
            $numeroView->year,
            $numeroView->increment,
            true,
            $sheet->getEvent()->getMode(),
            $sheet->getEvent()->getVat(),
            1000,
            1200,
            200,
            $sheet->getEvent()->getCurrency(),
            [],
            new \DateTime()
        );

        return $invoice;
    }
}
