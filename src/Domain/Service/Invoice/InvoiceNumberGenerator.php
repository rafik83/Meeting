<?php

namespace Proximum\Vimeet\Domain\Service\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;

class InvoiceNumberGenerator
{
    /**
     * Generate Invoice Number
     *
     * @param Invoice $invoice|null
     *
     * @return string
     */
    public static function generate(Invoice $invoice = null)
    {
        if (null !== $invoice) {
            return $invoice->getInvoiceIncrement() + 1;
        }

        return 1;
    }
}
