<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Summary;

class PromotionCodeView
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var int
     */
    public $total;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * PromotionCodeView constructor.
     *
     * @param string $title
     * @param string $description
     * @param int    $total
     * @param int    $quantity
     * @param string $currency
     * @param string $vatMode
     */
    public function __construct($title, $description, $total, $currency, $vatMode, $quantity = 1)
    {
        $this->title       = $title;
        $this->description = $description;
        $this->total       = $total;
        $this->quantity    = $quantity;
        $this->currency    = $currency;
        $this->vatMode     = $vatMode;
    }
}
