<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Package\Vat;

use Proximum\Vimeet\Domain\Money\AmountFormatter;

class VatView
{
    /** @var float */
    public $vatRate;

    /** @var string */
    public $vatMode;

    /** @var float */
    public $total;

    /** @var float the value of the vat */
    public $totalVat;

    /**
     * @param float  $vatRate
     * @param string $vatMode
     * @param float  $total
     * @param float  $totalVat
     */
    public function __construct(
        float $vatRate,
        string $vatMode,
        float $total,
        float $totalVat
    ) {
        $this->vatRate = $vatRate;
        $this->vatMode = $vatMode;
        $this->total = $total;
        $this->totalVat = $totalVat;
    }

    /**
     * @param float $price
     */
    public function addToTotal(float $price): void
    {
        $this->total += $price;
        $this->totalVat = AmountFormatter::calculateRateAmount($this->total, $this->vatRate);
    }
}
