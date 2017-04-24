<?php

namespace Proximum\Vimeet\Application\Query\Group\Participant;

use Proximum\Vimeet\Domain\Model\Sheet;

class SheetsViewQuery
{
    /** @var Sheet[] $sheets */
    public $sheets;

    /**
     * @param Sheet[] $sheets
     */
    public function __construct($sheets)
    {
        $this->sheets = $sheets;
    }
}
