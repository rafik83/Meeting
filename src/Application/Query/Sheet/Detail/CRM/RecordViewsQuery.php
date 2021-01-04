<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail\CRM;

use Proximum\Vimeet\Domain\Model\Sheet;

class RecordViewsQuery
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
