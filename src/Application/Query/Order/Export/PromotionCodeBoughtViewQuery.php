<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
