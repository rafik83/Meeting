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
     * @param int   $step
     */
    public function __construct(Cart $cart, Sheet $sheet, $step)
    {
        $this->cart  = $cart;
        $this->step  = $step;
        $this->sheet = $sheet;

        $template      = $cart->getTemplate();
        $stepTemplate  = $template[$step]['template'];
        $stepData      = array_combine(array_keys($stepTemplate), array_fill(0, count($stepTemplate), null));
        $sheetData     = isset($cart->getData()[$step]) ? $cart->getData()[$step] : $stepData;

        foreach ($stepData as $key => $value) {
            $this->packageData[$key] = isset($sheetData[$key]) ? $sheetData[$key] : null;
        }
    }
}
