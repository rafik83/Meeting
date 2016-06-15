<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class CartRow
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var int
     */
    private $quantity = 0;

    /**
     * @var string
     */
    private $serializedProduct;

    /**
     * @param Sheet   $sheet
     * @param Product $product
     * @param int     $quantity
     */
    public function __construct(Sheet $sheet, Product $product, $quantity)
    {
        $this->sheet             = $sheet;
        $this->quantity          = $quantity;
        $this->product           = $product;
        $this->serializedProduct = $product->getSerializedData();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set product
     *
     * @param Product $product
     *
     * @return CartRow
     */
    public function setProduct($product)
    {
        $this->product           = $product;
        $this->serializedProduct = $product->getSerializedData();

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return CartRow
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Add quantity
     *
     * @param int $quantity
     *
     * @return CartRow
     */
    public function addQuantity($quantity)
    {
        $this->quantity += $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getSerializedProduct()
    {
        return $this->serializedProduct;
    }
}
