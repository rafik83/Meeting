<?php

namespace Proximum\Vimeet\Application\Components\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class InvoiceUrlViewQuery
{
    /** @var Invoice */
    public $invoice;

    /**
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }
}
