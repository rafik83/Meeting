<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order;

class RemovePromotionCode implements Command
{
    /** @var Order\PromotionCode */
    public $promotionCode;

    /** @var Order */
    public $order;

    public function __construct(Order\PromotionCode $promotionCode, Order $order)
    {
        $this->promotionCode = $promotionCode;
        $this->order = $order;
    }
}
