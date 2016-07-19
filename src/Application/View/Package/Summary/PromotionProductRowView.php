<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class PromotionProductRowView
{
    /**
     * @var string
     */
    public $product;

    /**
     * @var string
     */
    public $promotionType;

    /**
     * @var int|float
     */
    public $discountValue;

    /**
     * @var int
     */
    public $quantity;

    /**
     * PromotionProductListView constructor.
     *
     * @param string    $product
     * @param string    $promotionType
     * @param int|float $discountValue
     * @param int       $quantity
     */
    public function __construct($product, $promotionType, $discountValue, $quantity)
    {
        $this->product       = $product;
        $this->promotionType = $promotionType;
        $this->quantity      = $quantity;
        $this->discountValue = $discountValue;
    }
}
