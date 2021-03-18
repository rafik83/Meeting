<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetIndicatorsViewQuery
{
    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
