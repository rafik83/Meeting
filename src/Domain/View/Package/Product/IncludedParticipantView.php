<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View\Package\Product;

class IncludedParticipantView
{
    /** @var int */
    public $totalQuantity;

    /** @var int */
    public $remainingQuantity;

    /**
     * @param int $totalQuantity
     * @param int $remainingQuantity
     */
    public function __construct($totalQuantity, $remainingQuantity)
    {
        $this->totalQuantity     = $totalQuantity;
        $this->remainingQuantity = $remainingQuantity;
    }
}
