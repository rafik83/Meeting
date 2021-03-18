<?php

namespace Proximum\Vimeet\Tests\Domain\Event\Day;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DDayGuesserTest extends TestCase
{
    /**
     * @dataProvider getData()
     *
     * @param \DateTimeInterface $date
     * @param Event              $event
     * @param bool               $expected
     */
    public function testIsItDDay(\DateTimeInterface $date, Event $event, bool $expected)
    {
        $dDayGuesser = new DDayGuesser($date);
        $result      = $dDayGuesser->isItDDay($event);

        $this->assertEquals($expected, $result);
    }

    public function testIsItDDayAndFeatureDisabled()
    {
        $event     = EventFactory::createEvent();
        $day1Begin = new \DateTime('2017-08-08 10:00:00.000');
        $day1End   = new \DateTime('2017-08-08 18:00:00.000');

        $date = new \DateTime('2017-08-08 14:00:00.000');
        $day1 = new Day($event, $day1Begin, $day1End);

        $event->setDays([$day1]);

        $dDayGuesser = new DDayGuesser($date, false);
        $result      = $dDayGuesser->isItDDayAndFeatureEnabled($event);

        $this->assertEquals(false, $result);
    }

    public function getData()
    {
        $date1 = new \DateTime('2017-08-08 14:00:00.000');
        $date2 = new \DateTime('2017-08-10 14:00:00.000');

        $event  = EventFactory::createEvent();
        $event1 = EventFactory::createEvent();
        $event2 = EventFactory::createEvent();
        $event3 = EventFactory::createEvent();
        $event4 = EventFactory::createEvent();

        $day1Begin = new \DateTime('2017-08-08 10:00:00.000');
        $day1End   = new \DateTime('2017-08-08 18:00:00.000');

        $day2Begin = new \DateTime('2017-08-09 10:00:00.000');
        $day2End   = new \DateTime('2017-08-09 18:00:00.000');

        $day1 = new Day($event1, $day1Begin, $day1End);
        $day2 = new Day($event2, $day1Begin, $day1End);
        $day3 = new Day($event3, $day1Begin, $day1End);
        $day4 = new Day($event3, $day2Begin, $day2End);
        $day5 = new Day($event4, $day1Begin, $day1End);
        $day6 = new Day($event4, $day2Begin, $day2End);

        $event1->setDays([$day1]);
        $event2->setDays([$day2]);
        $event3->setDays([$day3, $day4]);
        $event4->setDays([$day5, $day6]);

        return [
            [$date1, $event, false],
            [$date2, $event1, false],
            [$date1, $event2, true],
            [$date2, $event3, false],
            [$date1, $event4, true],
        ];
    }
}
