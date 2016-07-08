<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model;
use Proximum\Vimeet\Domain\Model\Order;

class PromotionCode
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Model\PromotionCode
     */
    private $promotionCode;

    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $price;

    /**
     * PromotionCode constructor.
     *
     * @param Order               $order
     * @param Model\PromotionCode $promotionCode
     * @param int                 $quantity
     * @param float               $price
     */
    public function __construct(Order $order, Model\PromotionCode $promotionCode, $quantity, $price)
    {
        $this->order         = $order;
        $this->quantity      = $quantity;
        $this->promotionCode = $promotionCode;
        $this->data          = $promotionCode->getSerializedData();
        $this->price         = $price;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback = null)
    {
        return $this->getTranslatedValueOfData('label', $locale, $fallback);
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getDescription($locale, $fallback = null)
    {
        return $this->getTranslatedValueOfData('description', $locale, $fallback);
    }

    /**
     * @param string      $value
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    private function getTranslatedValueOfData($value, $locale, $fallback = null)
    {
        $data = json_decode($this->data, true);

        if (!isset($data['translations'])) {
            return '';
        }

        if (isset($data['translations'][$locale]) && isset($data['translations'][$locale][$value])) {
            return $data['translations'][$locale][$value];
        }

        if (null !== $fallback
            && isset($data['translations'][$fallback])
            && isset($data['translations'][$fallback][$value])
        ) {
            return $data['translations'][$fallback][$value];
        }

        return '';
    }
}
