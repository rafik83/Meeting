<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;

class StateListViewQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var array */
    public $filters;

    /** @var array */
    public $slotsToFilter;

    /**
     * StateListViewQuery constructor.
     *
     * @param Sheet $sheet
     * @param array $filters
     * @param array $slotsToFilter
     */
    public function __construct(Sheet $sheet, array $filters = [], array $slotsToFilter = [])
    {
        $this->sheet         = $sheet;
        $this->filters       = $filters;
        $this->slotsToFilter = $slotsToFilter;
    }
}
