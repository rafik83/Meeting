<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Cart;
use Proximum\Vimeet\Domain\Model\Sheet;

class AddProducts
{
    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var array
     */
    public $packageData = [];

    /**
     * @param Cart  $cart
     * @param Sheet $sheet
     */
    public function __construct(Cart $cart, Sheet $sheet)
    {
        $this->cart        = $cart;
        $this->sheet       = $sheet;
        $this->packageData = $cart->getData();
    }
}
