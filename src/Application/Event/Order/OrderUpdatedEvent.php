<?php

namespace Proximum\Vimeet\Application\Event\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Symfony\Component\EventDispatcher;

class OrderUpdatedEvent extends EventDispatcher\Event
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
