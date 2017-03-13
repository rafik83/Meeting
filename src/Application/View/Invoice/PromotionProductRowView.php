<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Invoice;

class PromotionProductRowView
{
    /** @var string */
    public $productLabel;

    /** @var string */
    public $promotionType;

    /** @var int|float */
    public $discountValue;

    /** @var int */
    public $quantity;

    /**
     * @param string    $productLabel
     * @param string    $promotionType
     * @param int|float $discountValue
     * @param int       $quantity
     */
    public function __construct($productLabel, $promotionType, $discountValue, $quantity)
    {
        $this->productLabel  = $productLabel;
        $this->promotionType = $promotionType;
        $this->discountValue = $discountValue;
        $this->quantity      = $quantity;
    }
}
