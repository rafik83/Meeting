<?php

namespace Proximum\Vimeet\Tests\Domain\Happening;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Happening\HappeningDateHelper;

class HappeningDateHelperTest extends TestCase
{
    /**
     * @dataProvider provideTestGetHour
     */
    public function testGetHour($expected, $datetime, $locale, $timezone)
    {
        $actual = HappeningDateHelper::getHour($datetime, $locale, $timezone);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider provideTestGetDay
     */
    public function testGetDay($expected, $datetime, $locale, $timezone)
    {
        $actual = HappeningDateHelper::getDay($datetime, $locale, $timezone);
        $this->assertEquals($expected, $actual);
    }

    public function provideTestGetHour()
    {
        $datetime = new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC'));
        $datetime2 = new \DateTime('2017-06-22 8:10:45', new \DateTimeZone('UTC'));

        return [
            ['12:51', $datetime, 'fr', 'Europe/Paris'],
            ['12:51 PM', $datetime, 'en', 'Europe/Paris'],
            ['11:51 AM', $datetime, 'en', 'Europe/London'],
            ['9:10 AM', $datetime2, 'en', 'Europe/London'],
            ['12:51', $datetime, null, 'Europe/Paris'],
        ];
    }

    public function provideTestGetDay()
    {
        $datetime = new \DateTime('2017-06-22 10:51:45', new \DateTimeZone('UTC'));
        $datetime2 = new \DateTime('2017-01-05 10:51:45', new \DateTimeZone('UTC'));

        return [
            ['22/06/2017', $datetime, 'fr', 'Europe/Paris'],
            ['6/22/17', $datetime, 'en', 'Europe/Paris'],
            ['6/22/17', $datetime, 'en', 'Europe/London'],
            ['1/5/17', $datetime2, 'en', 'Europe/London'],
        ];
    }
}
