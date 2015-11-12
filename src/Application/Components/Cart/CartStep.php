<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Cart;

use Doctrine\Common\Collections\ArrayCollection;

class CartStep
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $subTotal;

    /**
     * @var ArrayCollection
     */
    private $cartRows;

    public function __construct($title, $subTotal)
    {
        $this->title    = $title;
        $this->subTotal = $subTotal;
        $this->cartRows = new ArrayCollection();
    }

    /**
     * @param CartRow $cartRow
     */
    public function addCartRow(CartRow $cartRow)
    {
        $this->cartRows->add($cartRow);
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * @return ArrayCollection
     */
    public function getCartRows()
    {
        return $this->cartRows;
    }
}
