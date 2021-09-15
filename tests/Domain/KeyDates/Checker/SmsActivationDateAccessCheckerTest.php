<?php

namespace Proximum\Vimeet\Tests\Domain\KeyDates\Checker;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\KeyDates\Checker\SmsActivationDateAccessChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class SmsActivationDateAccessCheckerTest extends TestCase
{
    /**
     * @dataProvider provideDates
     *
     * @param \DateTimeInterface      $date
     * @param \DateTimeInterface|null $dateAgenda
     * @param bool                    $expected
     */
    public function testAllowedToAccess(\DateTimeInterface $date, \DateTimeInterface $dateAgenda = null, bool $expected)
    {
        $event = EventFactory::createEvent();
        $event->getConfiguration()->setDates(null, null, null, null, null, $dateAgenda);

        $agendaAccessChecker = new SmsActivationDateAccessChecker($date);
        $this->assertEquals($expected, $agendaAccessChecker->allowedToAccess($event));
    }

    public static function provideDates()
    {
        return [
            [new \DateTime(), null, false],
            [new \DateTime('2017-09-12 10:10'), new \DateTime('2017-10-12 10:10'), false],
            [new \DateTime('2017-10-14 10:10'), new \DateTime('2017-10-12 10:10'), true],
        ];
    }
}
