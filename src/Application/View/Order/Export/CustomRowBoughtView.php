<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order\Export;

class CustomRowBoughtView
{
    /** @var int */
    public $customRowId;

    /** @var float */
    public $unitPrice;

    /** @var int */
    public $quantity;

    /** @var float */
    public $total;

    /**
     * @param int   $customRowId
     * @param float $unitPrice
     * @param int   $quantity
     * @param float $total
     */
    public function __construct(
        $customRowId,
        $unitPrice,
        $quantity,
        $total
    ) {
        $this->customRowId = $customRowId;
        $this->unitPrice   = $unitPrice;
        $this->quantity    = $quantity;
        $this->total       = $total;
    }
}
