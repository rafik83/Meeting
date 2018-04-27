<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class PromotionCodesView
{
    /**
     * @var PromotionCodeView[]
     */
    public $promotionCodes;

    /**
     * PromotionCodesView constructor.
     *
     * @param PromotionCodeView[] $promotionCodes
     */
    public function __construct(array $promotionCodes)
    {
        $this->promotionCodes = $promotionCodes;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->promotionCodes as $promotionCodeView) {
            $total += $promotionCodeView->total;
        }

        return $total;
    }
}
