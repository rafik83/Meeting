<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class Promotion
{
    const TYPE_PERCENT_OFF = 'percent_off';
    const TYPE_VALUE_OFF   = 'value_off';
    const TYPE_FREE        = 'free';

    /**
     * @var int
     */
    private $id;

    /**
     * @var PromotionCode
     */
    private $promotionCode;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $type;

    /**
     * @var float
     */
    private $value;

    /**
     * @var int
     */
    private $quantity = 1;

    /**
     * Promotion constructor.
     *
     * @param PromotionCode $promotionCode
     * @param Product       $product
     * @param string        $type
     * @param float         $value
     */
    public function __construct(PromotionCode $promotionCode, Product $product, $type, $value)
    {
        $this->promotionCode = $promotionCode;
        $this->product       = $product;
        $this->type          = $type;
        $this->value         = $type === self::TYPE_FREE ? null : $value;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get promotionCode
     *
     * @return PromotionCode
     */
    public function getPromotionCode()
    {
        return $this->promotionCode;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param $type
     * @param $value
     *
     * @return Promotion
     */
    public function update($type, $value)
    {
        $this->type  = $type;
        $this->value = $type === self::TYPE_FREE ? null : $value;

        return $this;
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENT_OFF:
                $discount = round($this->product->getUnitPrice() * $this->getValue() / 100, 2);
                break;
            case self::TYPE_VALUE_OFF:
                $discount = $this->product->getUnitPrice() - $this->getValue();
                break;
            case self::TYPE_FREE:
                $discount = $this->product->getUnitPrice();
                break;
        }

        return ($discount <= 0) ? $this->getValue() : $discount;
    }
}
