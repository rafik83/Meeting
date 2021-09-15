<?php

namespace Proximum\Vimeet\Application\Query\Invoice\BillingInfos;

use Proximum\Vimeet\Domain\Model\Sheet;

class BillingInfosQuery
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
