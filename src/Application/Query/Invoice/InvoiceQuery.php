<?php

namespace Proximum\Vimeet\Application\Query\Invoice;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class InvoiceQuery implements Query
{
    /** @var Invoice */
    public $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
