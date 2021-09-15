<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\AvailableSheets;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantAndDayQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;

class AvailableSlotsByParticipantAndDayQueryHandlerTest extends TestCase
{
    public function testHandleNotDDay()
    {
        $currentTime = new \DateTime('11/12/2013 10:30:00');
        $begin  = new \DateTime('11/12/2013 00:01:00');
        $end    = new \DateTime('11/12/2013 19:01:00');

        $event = EventFactory::createEvent();
        $event->setDays([new Day($event, $begin, $end)]);
        $day = $event->getFirstDay();
        $sheet = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $meetingRequestAccessChecker = $this->prophesize(MeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event)
            ->shouldNotBeCalled();

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $dDayGuesser = $this->prophesize(DDayGuesser::class);

        $query   = new AvailableSlotsByParticipantAndDayQuery($event, $participant, $day);
        $handler = new AvailableSlotsByParticipantAndDayQueryHandler(
            $dDayGuesser->reveal(),
            $meetingSlotRepository->reveal(),
            $currentTime,
            $meetingRequestAccessChecker->reveal()
        );

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldNotBeCalled()
        ;
        $dDayGuesser
            ->isItDDayAndFeatureEnabled($event)
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $result = $handler->handle($query);

        $this->assertEquals([], $result);
    }

    public function testHandle()
    {
        $currentTime = new \DateTime('11/12/2013 10:30:00');
        $begin  = new \DateTime('11/12/2013 00:01:00');
        $end    = new \DateTime('11/12/2013 19:01:00');
        $begin2 = new \DateTime('12/12/2013 00:01:00');
        $end2   = new \DateTime('12/12/2013 19:01:00');
        $slotBegin1 = new \DateTime('11/12/2013 10:00:00');
        $slotEnd1   = new \DateTime('11/12/2013 11:00:00');
        $slotBegin2 = new \DateTime('11/12/2013 11:00:00');
        $slotEnd2   = new \DateTime('11/12/2013 12:00:00');

        $event = EventFactory::createEvent();
        $event->setDays([
            new Day($event, $begin, $end),
            new Day($event, $begin2, $end2),
        ]);
        $day = $event->getFirstDay();
        $sheet = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $meetingRequestAccessChecker = $this->prophesize(MeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event)
            ->shouldBeCalled()
            ->willReturn(true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $dDayGuesser = $this->prophesize(DDayGuesser::class);

        $slot1 = SlotFactory::createSlot(1, $event, $slotBegin1, $slotEnd1);
        $slot2 = SlotFactory::createSlot(2, $event, $slotBegin2, $slotEnd2);

        $availableSlots = [$slot1, $slot2];
        $expected = [new AvailableSlotView(2, $slot2->getBegin(), $slot2->getEnd())];
        $query    = new AvailableSlotsByParticipantAndDayQuery($event, $participant, $day);
        $handler  = new AvailableSlotsByParticipantAndDayQueryHandler(
            $dDayGuesser->reveal(),
            $meetingSlotRepository->reveal(),
            $currentTime,
            $meetingRequestAccessChecker->reveal()
        );

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldBeCalled()
            ->willReturn($availableSlots);
        $dDayGuesser
            ->isItDDayAndFeatureEnabled($event)
            ->shouldBeCalled()
            ->willReturn(true);

        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithSlotPastTime()
    {
        $currentTime = new \DateTime('11/12/2013 18:00:00');
        $begin       = new \DateTime('11/12/2013 00:01:00');
        $end         = new \DateTime('11/12/2013 19:01:00');
        $slotBegin1  = new \DateTime('11/12/2013 10:00:00');
        $slotEnd1    = new \DateTime('11/12/2013 11:00:00');
        $slotBegin2  = new \DateTime('11/12/2013 13:00:00');
        $slotEnd2    = new \DateTime('11/12/2013 14:00:00');

        $event = EventFactory::createEvent();
        $event->setDays([new Day($event, $begin, $end)]);
        $day = $event->getFirstDay();

        $meetingRequestAccessChecker = $this->prophesize(MeetingRequestAccessChecker::class);
        $meetingRequestAccessChecker->allowedToAccess($event)
            ->shouldBeCalled()
            ->willReturn(true);

        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $dDayGuesser = $this->prophesize(DDayGuesser::class);

        $slot1 = SlotFactory::createSlot(1, $event, $slotBegin1, $slotEnd1);
        $slot2 = SlotFactory::createSlot(2, $event, $slotBegin2, $slotEnd2);

        $availableSlots = [$slot1, $slot2];
        $expected = [];

        $query   = new AvailableSlotsByParticipantAndDayQuery($event, $participant, $day);
        $handler = new AvailableSlotsByParticipantAndDayQueryHandler(
            $dDayGuesser->reveal(),
            $meetingSlotRepository->reveal(),
            $currentTime,
            $meetingRequestAccessChecker->reveal()
        );

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldBeCalled()
            ->willReturn($availableSlots);
        $dDayGuesser
            ->isItDDayAndFeatureEnabled($event)
            ->shouldBeCalled()
            ->willReturn(true);

        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }
}
