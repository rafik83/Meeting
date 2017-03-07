<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Invoice;

use Proximum\Vimeet\Domain\Model\Sheet;

interface InvoiceRepositoryInterface
{
    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function hasInvoice(Sheet $sheet);
}
