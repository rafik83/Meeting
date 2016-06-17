<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package;

class OptionView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var string
     */
    public $heading;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $addon;

    /**
     * @var string
     */
    public $image;

    /**
     * @var int
     */
    public $availabilityCurrent;

    /**
     * @var int
     */
    public $availabilityMax;

    /**
     * @var bool
     */
    public $isOutOfStock;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param int    $id
     * @param string $title
     * @param float  $unitPrice
     * @param string $heading
     * @param string $description
     * @param string $addon
     * @param string $image
     * @param int    $availabilityCurrent
     * @param int    $availabilityMax
     * @param bool   $isOutOfStock
     * @param string $vatMode
     * @param string $currency
     */
    public function __construct(
        $id,
        $title,
        $unitPrice,
        $heading,
        $description,
        $addon,
        $image,
        $availabilityCurrent,
        $availabilityMax,
        $isOutOfStock,
        $vatMode,
        $currency
    ) {
        $this->id                  = $id;
        $this->title               = $title;
        $this->unitPrice           = $unitPrice;
        $this->heading             = $heading;
        $this->description         = $description;
        $this->addon               = $addon;
        $this->image               = $image;
        $this->availabilityCurrent = $availabilityCurrent;
        $this->availabilityMax     = $availabilityMax;
        $this->isOutOfStock        = $isOutOfStock;
        $this->vatMode             = $vatMode;
        $this->currency            = $currency;
    }
}
