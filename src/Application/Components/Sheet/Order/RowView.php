<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Order;

class RowView
{
    /**
     * @var string
     */
    public $label;

    /**
     * @var float
     */
    public $unitPrice;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var RowView[]
     */
    public $included = [];

    /**
     * @var bool
     */
    public $editable = false;

    /**
     * @var \DateTimeInterface
     */
    public $updatableUntil = null;

    /**
     * @var bool
     */
    public $updatable = false;

    /**
     * RowView constructor.
     *
     * @param string                  $label
     * @param float                   $unitPrice
     * @param int                     $quantity
     * @param null|\DateTimeInterface $updatableUntil
     * @param bool                    $updatable
     */
    public function __construct($label, $unitPrice, $quantity, $updatableUntil, $updatable)
    {
        $this->label          = $label;
        $this->unitPrice      = $unitPrice;
        $this->quantity       = $quantity;
        $this->updatableUntil = $updatableUntil;
        $this->updatable      = $updatable;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->unitPrice * $this->quantity;
    }

    /**
     * @param RowView $included
     *
     * @return RowView
     */
    public function addIncluded(RowView $included)
    {
        $this->included[] = $included;

        return $this;
    }
}
