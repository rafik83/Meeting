<?php

namespace Proximum\Vimeet\Application\Query\Sheet\Detail;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetMeetingIndicatorQuery
{
    /** @var Sheet */
    public $sheet;

    /**
     * SheetMeetingIndicatorQuery constructor.
     *
     * @param Sheet $sheet
     */
    public function __construct(Sheet $sheet)
    {
        $this->sheet = $sheet;
    }
}
