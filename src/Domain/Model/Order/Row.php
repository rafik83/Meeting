<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;

class Row
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
     * @var string
     */
    private $data;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $price;

    /**
     * @var null|int
     */
    private $groupId;

    /**
     * Row constructor.
     *
     * @param Order    $order
     * @param Product  $product
     * @param int      $quantity
     * @param int|null $groupId
     */
    public function __construct(Order $order, Product $product, $quantity, $groupId = null)
    {
        $this->order    = $order;
        $this->quantity = $quantity;
        $this->data     = $product->getSerializedData();
        $this->product  = $product;
        $this->price    = $product->getUnitPrice();
        $this->groupId  = $groupId;
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
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
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
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return null|int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        $data = json_decode($this->data, true);

        return isset($data['type']) ? $data['type'] : null;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        if (null === $this->getProduct()) {
            return null;
        }

        return $this->getProduct()->getId();
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback = null)
    {
        $data = json_decode($this->data, true);

        if (!isset($data['translations'])) {
            return '';
        }

        if (isset($data['translations'][$locale]) && isset($data['translations'][$locale]['title'])) {
            return $data['translations'][$locale]['title'];
        }

        if (null !== $fallback
            && isset($data['translations'][$fallback])
            && isset($data['translations'][$fallback]['title'])
        ) {
            return $data['translations'][$fallback]['title'];
        }

        return '';
    }

    /**
     * @return bool
     */
    public function hasIncludedProduct()
    {
        $data = json_decode($this->data, true);

        return isset($data['productsIncluded']) && !empty($data['productsIncluded']) ? true : false;
    }
}
