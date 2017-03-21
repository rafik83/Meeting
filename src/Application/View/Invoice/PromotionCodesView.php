<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class PromotionCodesView
{
    /** @var PromotionCodeView[] */
    public $promotionCodeViews = [];

    /**
     * @param PromotionCodeView[] $promotionCodeViews
     */
    public function __construct(array $promotionCodeViews)
    {
        $this->promotionCodeViews = $promotionCodeViews;
    }
}
