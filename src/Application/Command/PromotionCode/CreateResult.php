<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
