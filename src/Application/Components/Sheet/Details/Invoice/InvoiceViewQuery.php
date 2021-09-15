<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Details\Invoice;

use Proximum\Vimeet\Domain\Model\Sheet;

class InvoiceViewQuery
{
    /** @var Sheet */
    public $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
