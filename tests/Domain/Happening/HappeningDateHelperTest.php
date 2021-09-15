<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;

class HappeningDateHelperTest extends TestCase
{
    /**
     * @dataProvider provideTestGetHour
     */
    public function testGetHour($expected, $dateTime, $locale, $timezone)
    {
        $actual = HappeningDateHelper::getHour($dateTime, $locale, $timezone);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideTestGetDay
     */
    public function testGetDay($expected, $dateTime, $locale, $timezone)
    {
        $actual = HappeningDateHelper::getDay($dateTime, $locale, $timezone);
        $this->assertEquals($expected, $actual);
    }

    public function provideTestGetHour()
    {
        $dateTime = new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC'));
        $dateTime2 = new \DateTime('2017-06-22 8:10:45', new \DateTimeZone('UTC'));

        return [
            ['12:51', $dateTime, 'fr', 'Europe/Paris'],
            ['12:51 PM', $dateTime, 'en', 'Europe/Paris'],
            ['11:51 AM', $dateTime, 'en', 'Europe/London'],
            ['9:10 AM', $dateTime2, 'en', 'Europe/London'],
            ['12:51', $dateTime, null, 'Europe/Paris'],
        ];
    }

    public function provideTestGetDay()
    {
        $dateTime = new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC'));
        $dateTime2 = new \DateTime('2017-01-05 10:51:45', new \DateTimeZone('UTC'));

        return [
            ['22/06/2017', $dateTime, 'fr', 'Europe/Paris'],
            ['6/22/17', $dateTime, 'en', 'Europe/Paris'],
            ['6/22/17', $dateTime, 'en', 'Europe/London'],
            ['1/5/17', $dateTime2, 'en', 'Europe/London'],
        ];
    }
}
