<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Domain\Model\Order;

class AddRowToProduct extends AbstractAddRow
{
    /**
     * @var Order\Row
     */
    public $row;

    /**
     * @param Order     $order
     * @param Order\Row $row
     */
    public function __construct(Order $order, Order\Row $row)
    {
        $this->order = $order;
        $this->row   = $row;
    }
}
