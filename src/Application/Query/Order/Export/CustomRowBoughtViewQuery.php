<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Order\Row;

class CustomRowBoughtViewQuery
{
    /** @var Row */
    public $row;

    /**
     * @param Row $row
     */
    public function __construct(Row $row)
    {
        $this->row = $row;
    }
}
