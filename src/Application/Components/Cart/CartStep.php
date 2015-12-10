<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

class CartStep
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var int
     */
    private $subTotal;

    /**
     * @var CartRow[]
     */
    private $cartRows;

    public function __construct($label, $subTotal)
    {
        $this->label    = $label;
        $this->subTotal = $subTotal;
        $this->cartRows = [];
    }

    /**
     * @param CartRow $cartRow
     */
    public function addCartRow(CartRow $cartRow)
    {
        array_push($this->cartRows, $cartRow);
        $this->addToSubTotal($cartRow->getSubTotal());
    }

    /**
     * @param int $subTotal
     */
    public function addToSubTotal($subTotal)
    {
        $this->subTotal += $subTotal;
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
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @return CartRow[]
     */
    public function getCartRows()
    {
        return $this->cartRows;
    }
}
