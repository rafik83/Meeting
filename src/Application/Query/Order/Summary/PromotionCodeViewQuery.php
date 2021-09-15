<?php

namespace Proximum\Vimeet\Application\Query\Order\Summary;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Order\PromotionCode;

class PromotionCodeViewQuery
{
    /**
     * @var PromotionCode
     */
    public $promotionCode;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var Order
     */
    public $order;

    /**
     * @param PromotionCode $promotionCode
     * @param string        $locale
     * @param Order         $order
     */
    public function __construct(PromotionCode $promotionCode, $locale, Order $order)
    {
        $this->promotionCode = $promotionCode;
        $this->locale        = $locale;
        $this->order         = $order;
    }
}
