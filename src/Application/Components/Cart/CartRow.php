<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

class CartRow
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var int
     */
    private $unitPrice;

    /**
     * @var int
     */
    private $subTotal;

    public function __construct($title, $quantity, $unitPrice)
    {
        $this->title     = $title;
        $this->quantity  = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subTotal  = $quantity * $unitPrice;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return int
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }
}
