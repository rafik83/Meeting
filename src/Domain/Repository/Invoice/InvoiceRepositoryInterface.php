<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

interface InvoiceRepositoryInterface
{
    /**
     * @return Invoice $invoice
     */
    public function add(Invoice $invoice);

    /**
     * Get last generated invoice for given event invoice prefix
     *
     * @param $invoicePrefix
     *
     * @return Invoice|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastInvoiceForEventPrefix($invoicePrefix);
}
