<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class PromotionCodeView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $description;

    /**
     * @var float
     */
    public $total;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * PromotionCodeView constructor.
     *
     * @param string $label
     * @param string $description
     * @param float  $total
     * @param int    $quantity
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct($label, $description, $total, $quantity, $vatMode, $currency)
    {
        $this->label       = $label;
        $this->description = $description;
        $this->total       = $total;
        $this->quantity    = $quantity;
        $this->vatMode     = $vatMode;
        $this->currency    = $currency;
    }
}
