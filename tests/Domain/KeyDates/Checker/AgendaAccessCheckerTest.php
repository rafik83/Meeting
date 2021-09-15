<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\AgendaAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AgendaAccessCheckerTest extends TestCase
{
    /**
     * @dataProvider provideDates
     */
    public function testAllowedToAccess($date, $dateAgenda, $expected)
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, null, null, $dateAgenda);

        $agendaAccessChecker = new AgendaAccessChecker($date);
        $this->assertEquals($expected, $agendaAccessChecker->allowedToAccess($event));
    }

    public static function provideDates()
    {
        return [
            [new \DateTime(), null, false], // No date set on event
            [new \DateTime('2016-09-12 10:10'), new \DateTime('2016-10-12 10:10'), false], // Date in the future
            [new \DateTime('2016-10-14 10:10'), new \DateTime('2016-10-12 10:10'), true], // Date had already passed
        ];
    }
}
