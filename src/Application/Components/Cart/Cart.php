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

class Cart
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var CartStep[]
     */
    private $cartSteps;

    /**
     * @param int $total
     */
    public function __construct($total)
    {
        $this->total     = $total;
        $this->cartSteps = [];
    }

    /**
     * @param CartStep $cartStep
     */
    public function addCartStep(CartStep $cartStep)
    {
        $this->cartSteps->add($cartStep);
        $this->addToTotal($cartStep->getSubTotal());
    }

    /**
     * @param int $total
     */
    public function addToTotal($total)
    {
        $this->total += $total;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return CartStep[]
     */
    public function getCartSteps()
    {
        return $this->cartSteps;
    }
}
