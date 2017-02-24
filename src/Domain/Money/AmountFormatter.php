<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Money;

class AmountFormatter
{
    /**
     * Get decimal amount and return an integer amount in centimes
     * Examples : floatToIntAmount(12.59) will return 1259
     *            floatToIntAmount(12)    will return 1200
     *
     * @param float $amount
     *
     * @return int
     */
    public static function decimalToCentimesAmount($amount)
    {
        return (int) 100 * $amount;
    }
}
