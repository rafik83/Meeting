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

class UpdateStep
{
    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var int
     */
    public $step;

    /**
     * @var array
     */
    public $packageData = [];

    /**
     * @param Cart $cart
     * @param int   $step
     */
    public function __construct(Cart $cart, $step)
    {
        $this->cart = $cart;
        $this->step  = $step;

        $template      = $cart->getTemplate();
        $stepTemplate  = $template[$step]['template'];
        $stepData      = array_combine(array_keys($stepTemplate), array_fill(0, count($stepTemplate), null));
        $sheetData     = isset($cart->getData()[$step]) ? $cart->getData()[$step] : $stepData;

        foreach ($stepData as $key => $value) {
            $this->packageData[$key] = isset($sheetData[$key]) ? $sheetData[$key] : null;
        }
    }
}
