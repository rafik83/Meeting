<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Exception\Invoice;

class CanNotExplodeNotValidNumeroInvoiceException extends InvoiceException
{
    /**
     * @param string $numero
     */
    public function __construct($numero)
    {
        parent::__construct();

        $this->message = sprintf('Can not explode not valid numero of invoice given %s', $numero);
    }
}
