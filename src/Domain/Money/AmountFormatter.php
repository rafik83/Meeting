<?php

namespace Proximum\Vimeet\Domain\Money;

class AmountFormatter
{
    /**
     * Get decimal amount and return an integer amount in cents
     * Examples : decimalToCentsAmount(12.59) will return 1259
     *            decimalToCentsAmount(12)    will return 1200
     *
     * @param float $amount
     *
     * @return int
     */
    public static function decimalToCentsAmount(float $amount): int
    {
        // (int) always rounds the number down, it is fixed by round() method
        return (int) round(100 * $amount);
    }

    /**
     * @param int $amount
     *
     * @return float|int
     */
    public static function centsToDecimalAmount(int $amount): float
    {
        return $amount / 100;
    }

    /**
     * @param int   $amount in cents
     * @param float $rate
     *
     * @return int
     */
    public static function calculateRateAmount(int $amount, float $rate): int
    {
        return (int) round($amount * $rate / 100);
    }
}
