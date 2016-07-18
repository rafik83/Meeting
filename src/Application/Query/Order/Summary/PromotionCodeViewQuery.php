<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Order\Summary;

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
     * @param PromotionCode $promotionCode
     * @param string        $locale
     */
    public function __construct(PromotionCode $promotionCode, $locale)
    {
        $this->promotionCode = $promotionCode;
        $this->locale        = $locale;
    }
}
