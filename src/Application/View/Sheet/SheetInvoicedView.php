<?php

namespace Proximum\Vimeet\Application\View\Sheet;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetInvoicedView
{
    /** @var Sheet */
    public $sheet;

    /** @var Invoice[] */
    public $invoices;

    /**
     * @param Sheet     $sheet
     * @param Invoice[] $invoices
     */
    public function __construct(Sheet $sheet, array $invoices)
    {
        $this->sheet    = $sheet;
        $this->invoices = $invoices;
    }
}
