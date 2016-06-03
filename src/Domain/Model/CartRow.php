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
    private $quantity;

    /**
     * @var string
     */
    private $serializedProduct;

    /**
     * @var \DateTimeInterface
     */
    private $createdAt;

    /**
     * @param Sheet              $sheet
     * @param Product            $product
     * @param int                $quantity
     * @param \DateTimeInterface $createdAt
     */
    public function __construct(Sheet $sheet, Product $product, $quantity, \DateTimeInterface $createdAt)
    {
        $this->sheet             = $sheet;
        $this->product           = $product;
        $this->quantity          = $quantity;
        $this->createdAt         = $createdAt;
        $this->serializedProduct = json_encode($product);
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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getSerializedProduct()
    {
        return $this->serializedProduct;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
