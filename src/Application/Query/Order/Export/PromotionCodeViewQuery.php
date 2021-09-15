<?php

namespace Proximum\Vimeet\Application\Query\Order\Export;

use Proximum\Vimeet\Domain\Model\PromotionCode;

class PromotionCodeViewQuery
{
    /** @var PromotionCode */
    public $promotionCode;

    /** @var string */
    public $adminLocale;

    /**
     * @param PromotionCode $promotionCode
     * @param string        $adminLocale
     */
    public function __construct(PromotionCode $promotionCode, $adminLocale)
    {
        $this->promotionCode = $promotionCode;
        $this->adminLocale   = $adminLocale;
    }
}
