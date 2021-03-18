<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Attend;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetAttendanceViewQuery
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
