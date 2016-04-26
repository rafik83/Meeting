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
    private $label;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @var float
     */
    private $unitPrice;

    /**
     * @var float
     */
    private $subTotal;

    /**
     * @var CartRow[]
     */
    private $include;

    /**
     * CartRow constructor.
     *
     * @param string $label
     * @param int    $quantity
     * @param float  $unitPrice
     */
    public function __construct($label, $quantity, $unitPrice)
    {
        $this->label     = $label;
        $this->quantity  = $quantity;
        $this->unitPrice = $unitPrice;
        $this->subTotal  = $quantity * $unitPrice;
        $this->include   = [];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @return float
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @return CartRow[]
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @param CartRow $cartRow
     */
    public function addInclude(CartRow $cartRow)
    {
        $this->include[] = $cartRow;
    }
}
