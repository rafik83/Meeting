<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Money;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Money\AmountFormatter;

class AmountFormatterTest extends TestCase
{
    public function provideDecimalToCentsAmount()
    {
        return [
            ['1259', '12.59'],
            ['10','0.1'],
            ['1200','12'],
            ['100','1'],

        ];
    }

    /**
     * @dataProvider provideDecimalToCentsAmount
     *
     * @param $expected
     * @param $amount
     */
    public function testDecimalToCentsAmount($expected, $amount)
    {
        $actual = AmountFormatter::decimalToCentsAmount($amount);
        $this->assertEquals($expected, $actual);
    }

    public function provideCentsToDecimalAmount()
    {
        return [
            ['12.59', '1259'],
            ['1.2', '120'],
            ['1000', '100000'],
            ['1', '100'],
        ];
    }

    /**
     * @dataProvider provideCentsToDecimalAmount
     *
     * @param $expected
     * @param $amount
     */
    public function testCentsToDecimalAmount($expected, $amount)
    {
        $actual = AmountFormatter::centsToDecimalAmount($amount);
        $this->assertEquals($expected, $actual);
    }

    public function provideCalculateRateAmount()
    {
        return [
            ['4','200','2'],
            ['12','400','3'],
            ['600','5000','12'],
        ];
    }

    /**
     * @dataProvider provideCalculateRateAmount
     *
     * @param $expected
     * @param $amount
     * @param $rate
     */
    public function testCalculateRateAmount($expected, $amount, $rate)
    {
        $actual = AmountFormatter::calculateRateAmount($amount, $rate);
        $this->assertEquals($expected, $actual);
    }
}
