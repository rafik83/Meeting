<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Accommodation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Add;
use Proximum\Vimeet\Domain\Model\Event;

class AddTest extends TestCase
{
    public function test_has_no_duplicated_day()
    {
        $date1 = new \DateTime('2018-10-10 10:00:00.000');
        $date2 = new \DateTime('2018-10-11 10:00:00.000');

        $day1 = $this->prophesize(Event\Day::class);
        $day1->getBegin()->shouldBeCalled()->willReturn($date1);
        $day2 = $this->prophesize(Event\Day::class);
        $day2->getBegin()->shouldBeCalled()->willReturn($date2);

        $event = $this->prophesize(Event::class);
        $event->getFirstDay()->shouldBeCalled()->willReturn($day1->reveal());
        $event->getDays()
            ->shouldBeCalled()
            ->willReturn([
                $day1->reveal(),
                $day2->reveal()
            ]);

        $add = new Add($event->reveal());

        $this->assertFalse($add->hasDuplicatedDay());
    }

    public function test_has_duplicated_day()
    {
        $date1 = new \DateTime('2018-10-10 10:00:00.000');
        $day1 = $this->prophesize(Event\Day::class);
        $day1->getBegin()->shouldBeCalled()->willReturn($date1);

        $event = $this->prophesize(Event::class);
        $event->getFirstDay()->shouldBeCalled()->willReturn($day1->reveal());
        $event->getDays()
            ->shouldBeCalled()
            ->willReturn([
                $day1->reveal(),
            ]);

        $add = new Add($event->reveal());
        $add->overnightCapacities = [
            new AccommodationOvernightCapacityView($date1, 12),
            new AccommodationOvernightCapacityView($date1, 12),
        ];

        $this->assertTrue($add->hasDuplicatedDay());
    }
}
