<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Vat;

class VatView
{
    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $total;

    /** @var float */
    public $totalWithVat;

    /**
     * @param float  $vatRate
     * @param string $vatMode
     * @param float  $total
     * @param float  $totalWithVat
     */
    public function __construct(
        float $vatRate,
        string $vatMode,
        float $total,
        float $totalWithVat
    ) {
        $this->vatRate = $vatRate;
        $this->vatMode = $vatMode;
        $this->total = $total;
        $this->totalWithVat = $totalWithVat;
    }
}
