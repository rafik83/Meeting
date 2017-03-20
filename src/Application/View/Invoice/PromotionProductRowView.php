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
    const TYPE_PERCENT_OFF = 'percent_off';
    const TYPE_VALUE_OFF   = 'value_off';
    const TYPE_FREE        = 'free';

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

    /**
     * @return bool
     */
    public function isPercentOff()
    {
        return $this->promotionType === self::TYPE_PERCENT_OFF;
    }

    /**
     * @return bool
     */
    public function isValueOff()
    {
        return $this->promotionType === self::TYPE_VALUE_OFF;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return $this->promotionType === self::TYPE_FREE;
    }
}
