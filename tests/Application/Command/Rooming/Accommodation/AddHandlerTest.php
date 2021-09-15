<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Add;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AddHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Domain\Model\Rooming\AccommodationOvernightCapacity;
use Proximum\Vimeet\Domain\Repository\Rooming\AccommodationRepositoryInterface;

class AddHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $date1 = new \DateTime('2018-12-01 10:10:10.000');
        $date2 = new \DateTime('2018-12-02 10:10:10.000');
        $date3 = new \DateTime('2018-12-03 10:10:10.000');

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
        $add->title = 'Novotel';
        $add->overnightCapacities = [
            new AccommodationOvernightCapacityView($date1, 9),
            new AccommodationOvernightCapacityView($date2, 100),
            new AccommodationOvernightCapacityView($date3, 250)
        ];

        $expectedResult = new Accommodation($event->reveal(), 'Novotel');
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $date1, 9));
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $date2, 100));
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $date3, 250));

        $accommodationRepository = $this->prophesize(AccommodationRepositoryInterface::class);
        $accommodationRepository->add($expectedResult)->shouldBeCalled();

        $handler = new AddHandler($accommodationRepository->reveal());
        $handler->handle($add);
    }

    public function testNoDaysModification(): void
    {
        $datePreviousDay = new \DateTime('2018-11-30 10:10:10.000');
        $date1 = new \DateTime('2018-12-01 10:10:10.000');
        $date2 = new \DateTime('2018-12-02 10:10:10.000');

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
        $add->title = 'Novotel';

        $expectedResult = new Accommodation($event->reveal(), 'Novotel');
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $datePreviousDay, 0));
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $date1, 0));
        $expectedResult->addOvernightCapacity(new AccommodationOvernightCapacity($expectedResult, $date2, 0));

        $accommodationRepository = $this->prophesize(AccommodationRepositoryInterface::class);
        $accommodationRepository->add($expectedResult)->shouldBeCalled();

        $handler = new AddHandler($accommodationRepository->reveal());
        $handler->handle($add);
    }
}
