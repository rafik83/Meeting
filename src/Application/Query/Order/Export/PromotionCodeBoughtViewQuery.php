<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\Order\PromotionCode;

class PromotionCodeBoughtViewQuery
{
    /** @var PromotionCode */
    public $promotionCode;

    /**
     * @param PromotionCode $promotionCode
     */
    public function __construct(PromotionCode $promotionCode)
    {
        $this->promotionCode = $promotionCode;
    }
}
