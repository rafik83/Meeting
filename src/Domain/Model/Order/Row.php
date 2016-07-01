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
    private $product;

    /**
     * @var Product
     */
    private $linkedProduct;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $price;

    /**
     * Row constructor.
     *
     * @param Order   $order
     * @param Product $linkedProduct
     * @param int     $quantity
     */
    public function __construct(Order $order, Product $linkedProduct, $quantity)
    {
        $this->order         = $order;
        $this->quantity      = $quantity;
        $this->linkedProduct = $linkedProduct;
        $this->price         = $linkedProduct->getUnitPrice();
        $this->product       = $linkedProduct->getSerializedData();
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
     * @return Product
     */
    public function getLinkedProduct()
    {
        return $this->linkedProduct;
    }
}
