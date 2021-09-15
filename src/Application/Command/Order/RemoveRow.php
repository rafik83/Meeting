<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order\Row;

class RemoveRow implements Command
{
    /**
     * @var Row
     */
    public $row;

    /**
     * @param Row $row
     */
    public function __construct(Row $row)
    {
        $this->row = $row;
    }
}
