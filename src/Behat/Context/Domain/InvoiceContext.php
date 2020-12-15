<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\InvoiceContextProxyInterface;

class InvoiceContext implements Context
{
    /** @var InvoiceContextProxyInterface */
    private $invoiceContextProxy;

    /**
     * @param InvoiceContextProxyInterface $invoiceContextProxy
     */
    public function __construct(InvoiceContextProxyInterface $invoiceContextProxy)
    {
        $this->invoiceContextProxy = $invoiceContextProxy;
    }

    /**
     * @Given /^there is an invoice with the numero "(?P<numero>[^"]+)" for this sheet$/
     *
     * @param string $numero
     */
    public function thereIsAnInvoiceWithNumero($numero)
    {
        $sheet = $this->invoiceContextProxy->getStorage()->get('sheet');

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $order = $this->invoiceContextProxy->getStorage()->get('order');
        $invoice = $this->invoiceContextProxy->getInvoiceManager()->create($sheet->getEvent(), $numero, $sheet, $order);
        $this->invoiceContextProxy->getStorage()->set('invoice', $invoice);
    }
}
