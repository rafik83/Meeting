<?php

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
