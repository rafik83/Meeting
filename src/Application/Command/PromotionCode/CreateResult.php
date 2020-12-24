<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\PromotionCode;

class CreateResult
{
    /**
     * @var PromotionCode
     */
    public $promotionCode;

    /**
     * CreateResult constructor.
     *
     * @param PromotionCode $promotionCode
     */
    public function __construct(PromotionCode $promotionCode)
    {
        $this->promotionCode = $promotionCode;
    }
}
