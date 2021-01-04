<?php

namespace Proximum\Vimeet\Tests\Domain\Money;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Money\AmountFormatter;

class AmountFormatterTest extends TestCase
{
    /**
     * @dataProvider provideDecimalToCentsAmount
     */
    public function testDecimalToCentsAmount($expected, $amount)
    {
        $actual = AmountFormatter::decimalToCentsAmount($amount);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideCentsToDecimalAmount
     */
    public function testCentsToDecimalAmount($expected, $amount)
    {
        $actual = AmountFormatter::centsToDecimalAmount($amount);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideCalculateRateAmount
     */
    public function testCalculateRateAmount($expected, $amount, $rate)
    {
        $actual = AmountFormatter::calculateRateAmount($amount, $rate);
        $this->assertEquals($expected, $actual);
    }

    public function provideDecimalToCentsAmount()
    {
        return [
            [1259, 12.59],
            [10, 0.1],
            [1200, 12],
            [100, 1],
        ];
    }

    public function provideCentsToDecimalAmount()
    {
        return [
            [12.59, 1259],
            [1.2, 120],
            [1000, 100000],
            [1, 100],
        ];
    }

    public function provideCalculateRateAmount()
    {
        return [
            [4, 200, 2],
            [12, 400, 3],
            [600, 5000, 12],
        ];
    }
}
