<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class PromotionCodesView
{
    /**
     * @var PromotionCodeView[]
     */
    public $promotionCodes = [];

    /**
     * @param PromotionCodeView $promotionCode
     */
    public function addPromotionCode(PromotionCodeView $promotionCode)
    {
        $this->promotionCodes[] = $promotionCode;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return array_reduce($this->promotionCodes, function ($carry, PromotionCodeView $promotionCodeView) {
            return $carry + $promotionCodeView->total;
        }, 0);
    }
}
