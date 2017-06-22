<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Happening;

use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;

class HappeningDateHelperTest extends \PHPUnit_Framework_TestCase
{
    public function provideTestGetHour()
    {
        return [
            ['12:51', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'fr', 'Europe/Paris'],
            ['12:51 PM', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'en', 'Europe/Paris'],
            ['11:51 AM', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'en', 'Europe/London'],
            ['9:10 AM', new \DateTime('2017-01-05 9:10:45', new \DateTimeZone('UTC')), 'en', 'Europe/London'],
            ['12:51', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), null, 'Europe/Paris'],
        ];
    }

    /**
     * @dataProvider provideTestGetHour
     *
     * @param $expected
     * @param $datetime
     * @param $local
     * @param $timezone
     */
    public function testGetHour($expected, $datetime, $local, $timezone)
    {
        $actual = HappeningDateHelper::getHour($datetime, $local, $timezone);
        $this->assertEquals($expected, $actual);
    }

    public function provideTestGetDay()
    {
        return [
            ['22/06/2017', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'fr', 'Europe/Paris'],
            ['6/22/17', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'en', 'Europe/Paris'],
            ['6/22/17', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), 'en', 'Europe/London'],
            ['1/5/17', new \DateTime('2017-01-05 9:10:45', new \DateTimeZone('UTC')), 'en', 'Europe/London'],
            ['6/22/17', new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC')), null, 'Europe/Paris'],
        ];
    }


    /**
     * @dataProvider provideTestGetDay
     *
     * @param $expected
     * @param $datetime
     * @param $local
     * @param $timezone
     */
    public function testGetDay($expected, $datetime, $local, $timezone)
    {
        $actual= HappeningDateHelper::getDay($datetime, $local, $timezone);
        $this->assertEquals($expected, $actual);
    }
}
