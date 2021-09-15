<?php

namespace Proximum\Vimeet\Application\Query\Order\OrderVat;

use Proximum\Vimeet\Domain\Model\Order;

class OrderVatViewQuery
{
    /** @var Order */
    public $order;

    /**
     * @param $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
