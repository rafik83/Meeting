<?php

namespace Proximum\Vimeet\Tests\Application\Query\Planning;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\AssignmentViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\HappeningParticipationViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MassViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\MeetingViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQuery;
use Proximum\Vimeet\Application\Query\Planning\Day\UnavailabilityViewQueryHandler;
use Proximum\Vimeet\Application\Query\Planning\DayViewQuery;
use Proximum\Vimeet\Application\Query\Planning\DayViewQueryHandler;
use Proximum\Vimeet\Application\View\Planning\Day\AssignmentView;
use Proximum\Vimeet\Application\View\Planning\Day\HappeningParticipationView;
use Proximum\Vimeet\Application\View\Planning\Day\MassView;
use Proximum\Vimeet\Application\View\Planning\Day\MeetingView;
use Proximum\Vimeet\Application\View\Planning\Day\UnavailabilityView;
use Proximum\Vimeet\Application\View\Planning\DayView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot as Slot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability;
use Proximum\Vimeet\Domain\Model\Unavailability\MassAssignment;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DayViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $locale = 'fr';
        $begin = new \DateTime('2016-10-12 10:00');
        $end   = new \DateTime('2016-10-12 18:00');
        $day   = new Day($event, $begin, $end);
        $user  = $this->prophesize(User::class);

        $userSheet = $this->prophesize(Sheet::class);
        $sheetMet = $this->prophesize(Sheet::class);
        $userSheet->getTitle()->willReturn('userSheetTitle');
        $sheetMet->getTitle()->willReturn('sheetMetTitle');

        $participation = $this->prophesize(HappeningParticipation::class);
        $happening = $this->prophesize(Happening::class);
        $hb = new \DateTime('2016-10-12 11:00');
        $he = new \DateTime('2016-10-12 12:00');
        $happening->getBegin()->willReturn($hb);
        $happening->getEnd()->willReturn($he);
        $participation->getHappening()->willReturn($happening->reveal());

        $assignment = $this->prophesize(MassAssignment::class);
        $assignment->getBegin()->willReturn(new \DateTime('2016-10-12 16:00'));
        $assignment->getEnd()->willReturn(new \DateTime('2016-10-12 16:45'));

        $unavailability1 = $this->prophesize(Unavailability::class);
        $unavailability2 = $this->prophesize(Unavailability::class);
        $unavailability1->getBegin()->willReturn(new \DateTime('2016-10-12 13:00'));
        $unavailability1->getEnd()->willReturn(new \DateTime('2016-10-12 14:00'));
        $unavailability2->getBegin()->willReturn(new \DateTime('2016-10-14 13:00'));
        $unavailability2->getEnd()->willReturn(new \DateTime('2016-10-14 14:00'));

        $meeting1 = $this->prophesize(Meeting::class);
        $meeting2 = $this->prophesize(Meeting::class);
        $slot1 = $this->prophesize(Slot::class);
        $slot1->getBegin()->willReturn(new \DateTime('2016-10-14 13:00'));
        $slot1->getEnd()->willReturn(new \DateTime('2016-10-14 13:25'));
        $slot2 = $this->prophesize(Slot::class);
        $slot2->getBegin()->willReturn(new \DateTime('2016-10-12 13:00'));
        $slot2->getEnd()->willReturn(new \DateTime('2016-10-12 13:25'));
        $meeting1->getSlot()->willReturn($slot1);
        $meeting2->getSlot()->willReturn($slot2);

        $mass1   = $this->prophesize(Unavailability\Mass::class);
        $mass2   = $this->prophesize(Unavailability\Mass::class);
        $mass1->getBegin()->willReturn(new \DateTime('2016-10-12 13:00'));
        $mass2->getBegin()->willReturn(new \DateTime('2016-10-13 13:00'));
        $mass1->getEnd()->willReturn(new \DateTime('2016-10-12 13:30'));
        $mass2->getEnd()->willReturn(new \DateTime('2016-10-13 13:30'));

        $happenings       = [$participation->reveal()];
        $assignments      = [$assignment->reveal()];
        $unavailabilities = [$unavailability1->reveal(), $unavailability2->reveal()];
        $masses           = [$mass1->reveal(), $mass2->reveal()];
        $meetings         = [$meeting1->reveal(), $meeting2->reveal()];

        $happeningHandler = $this->prophesize(HappeningParticipationViewQueryHandler::class);
        $happeningHandler
            ->handle(new HappeningParticipationViewQuery($participation->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn(new HappeningParticipationView($hb, $he, 'title'))
        ;
        $massHandler = $this->prophesize(MassViewQueryHandler::class);
        $massHandler
            ->handle(new MassViewQuery($mass1->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn(new MassView($begin, $end, 'title'));
        $massHandler
            ->handle(new MassViewQuery($mass2->reveal(), $locale))
            ->shouldNotBeCalled();
        $assignmentHandler = $this->prophesize(AssignmentViewQueryHandler::class);
        $assignmentHandler
            ->handle(new AssignmentViewQuery($assignment->reveal(), $locale))
            ->shouldBeCalled()
            ->willReturn(new AssignmentView($begin, $end, 'title'));
        $unavailabilityHandler = $this->prophesize(UnavailabilityViewQueryHandler::class);
        $unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability1->reveal()))
            ->shouldBeCalled()
            ->willReturn(new UnavailabilityView($begin, $end));
        $unavailabilityHandler
            ->handle(new UnavailabilityViewQuery($unavailability2->reveal()))
            ->shouldNotBeCalled();
        $meetingHandler = $this->prophesize(MeetingViewQueryHandler::class);
        $meetingHandler
            ->handle(new MeetingViewQuery($event, $meeting2->reveal(), $user->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn(new MeetingView($begin, $end, 'spotRef', false, [], $userSheet->reveal(), $sheetMet->reveal()));
        $meetingHandler
            ->handle(new MeetingViewQuery($event, $meeting1->reveal(), $user->reveal(), 'fr'))
            ->shouldNotBeCalled();

        $handler = new DayViewQueryHandler(
            $happeningHandler->reveal(),
            $massHandler->reveal(),
            $assignmentHandler->reveal(),
            $unavailabilityHandler->reveal(),
            $meetingHandler->reveal()
        );
        $result = $handler->handle(new DayViewQuery(
            $event,
            $user->reveal(),
            $day,
            $locale,
            $unavailabilities,
            $happenings,
            $masses,
            $assignments,
            $meetings
        ));

        $expected = new DayView(
            $begin,
            $end,
            [new HappeningParticipationView($hb, $he, 'title')],
            [new UnavailabilityView($begin, $end)],
            [new MassView($begin, $end, 'title')],
            [new AssignmentView($begin, $end, 'title')],
            [new MeetingView($begin, $end, 'spotRef', false, [], $userSheet->reveal(), $sheetMet->reveal())]
        );

        $this->assertEquals($expected, $result);
    }
}
