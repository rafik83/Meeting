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
     * PromotionCodeRow ID
     *
     * @var int
     */
    public $id;

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
     * @var float
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
     * @param int    $id
     * @param string $title
     * @param string $description
     * @param float  $total
     * @param string $currency
     * @param string $vatMode
     * @param int    $quantity
     */
    public function __construct($id, $title, $description, $total, $currency, $vatMode, $quantity = 1)
    {
        $this->id          = $id;
        $this->title       = $title;
        $this->description = $description;
        $this->total       = $total;
        $this->quantity    = $quantity;
        $this->currency    = $currency;
        $this->vatMode     = $vatMode;
    }
}
