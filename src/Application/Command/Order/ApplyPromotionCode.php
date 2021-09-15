<?php

namespace Proximum\Vimeet\Application\Command\Order;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class ApplyPromotionCode implements Command
{
    /** @var null|PromotionCode */
    public $promotionCode;

    /** @var Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
