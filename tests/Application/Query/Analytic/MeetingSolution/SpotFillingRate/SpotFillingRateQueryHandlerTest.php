<?php

namespace Proximum\Vimeet\Tests\Application\Query\Analytic\MeetingSolution\SpotFillingRate;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQueryHandler;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateDayView;
use Proximum\Vimeet\Application\View\Analytic\MeetingSolution\Graph\SpotFillingRateSlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class SpotFillingRateQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $spotUnavailability1 = $this->prophesize(SpotUnavailability::class);
        $spotUnavailability2 = $this->prophesize(SpotUnavailability::class);
        $begin1 = new \DateTime('2017-07-10 10:00:00.000');
        $begin2 = new \DateTime('2017-07-10 11:00:00.000');
        $begin3 = new \DateTime('2017-07-11 12:00:00.000');
        $end1 = new \DateTime('2017-07-10 10:10:10.000');
        $end2 = new \DateTime('2017-07-10 11:10:10.000');
        $end3 = new \DateTime('2017-07-11 12:10:10.000');

        $event = $this->prophesize(Event::class);
        $event->getTimeZone()->willReturn('Europe/Paris');
        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot3 = $this->prophesize(MeetingSlot::class);

        $slot1->getBegin()->willReturn($begin1);
        $slot2->getBegin()->willReturn($begin2);
        $slot3->getBegin()->willReturn($begin3);

        $slot1->getEnd()->willReturn($end1);
        $slot2->getEnd()->willReturn($end2);
        $slot3->getEnd()->willReturn($end3);

        $slot1->getId()->willReturn(1);
        $slot2->getId()->willReturn(2);
        $slot3->getId()->willReturn(3);

        $spot1 = $this->prophesize(Spot::class);
        $spot2 = $this->prophesize(Spot::class);
        $spot3 = $this->prophesize(Spot::class);
        $spot1->getId()->willReturn(11);
        $spot2->getId()->willReturn(12);
        $spot3->getId()->willReturn(13);

        $spot1->getMeetingCapacity()->willReturn(3);
        $spot2->getMeetingCapacity()->willReturn(3);
        $spot3->getMeetingCapacity()->willReturn(3);

        $spot1->getSpotUnavailabilities()->willReturn([$spotUnavailability1->reveal()]);
        $spot2->getSpotUnavailabilities()->willReturn([]);
        $spot3->getSpotUnavailabilities()->willReturn([$spotUnavailability2->reveal()]);

        $spotUnavailability1->getSlot()->willReturn($slot2->reveal());
        $spotUnavailability2->getSlot()->willReturn($slot1->reveal());

        $slots = [$slot1->reveal(), $slot2->reveal(), $slot3->reveal()];
        $spots = [$spot1->reveal(), $spot2->reveal(), $spot3->reveal()];

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository
            ->countMeetingForSpotsAndSlot([$spot1->reveal(), $spot2->reveal()], $slot1->reveal())
            ->shouldBeCalled()
            ->willReturn(0)
        ;

        $meetingRepository
            ->countMeetingForSpotsAndSlot([$spot2->reveal(), $spot3->reveal()], $slot2->reveal())
            ->shouldBeCalled()
            ->willReturn(6)
        ;

        $meetingRepository
            ->countMeetingForSpotsAndSlot([$spot1->reveal(), $spot2->reveal(), $spot3->reveal()], $slot3->reveal())
            ->shouldBeCalled()
            ->willReturn(7)
        ;

        $handler = new SpotFillingRateQueryHandler($meetingRepository->reveal());
        $result = $handler->handle(new SpotFillingRateQuery($event->reveal(), $slots, $spots));

        $day1 = new SpotFillingRateDayView($begin1, 'Europe/Paris');
        $day2 = new SpotFillingRateDayView($begin3, 'Europe/Paris');
        $day1->addSlotFillingRate(new SpotFillingRateSlotView($begin1, $end1, 0));
        $day1->addSlotFillingRate(new SpotFillingRateSlotView($begin2, $end2, 100));
        $day2->addSlotFillingRate(new SpotFillingRateSlotView($begin3, $end3, 77));

        $expected = [
            '2017-07-10' => $day1,
            '2017-07-11' => $day2,
        ];

        $this->assertEquals($expected, $result);
    }
}
