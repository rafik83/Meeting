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
     * Get decimal amount and return an integer amount in cents
     * Examples : floatToIntAmount(12.59) will return 1259
     *            floatToIntAmount(12)    will return 1200
     *
     * @param float $amount
     *
     * @return int
     */
    public static function decimalToCentsAmount($amount)
    {
        // (int) always rounds the number down, it is fixed by round() method
        return (int) round(100 * $amount);
    }

    /**
     * @param int   $amount in cents
     * @param float $rate
     *
     * @return int
     */
    public static function calculateRateAmount($amount, $rate)
    {
        return (int) ($amount * $rate / 100);
    }
}
