<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class DecrementStock implements Command
{
    /** @var PromotionCode */
    public $promotionCode;

    public function __construct(PromotionCode $promotionCode)
    {
        $this->promotionCode = $promotionCode;
    }
}
