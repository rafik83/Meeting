<?php

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\AvailableSheets;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class AvailableSlotsByParticipantQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $dDayGuesser;

    /** @var ObjectProphecy */
    private $meetingSlotRepository;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $participant;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->dDayGuesser = $this->prophesize(DDayGuesser::class);
        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
    }

    public function testHandleNotDDay()
    {
        $dateTime = new \DateTime('2017-08-12 12:00:00.000');

        $expected = [];

        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(false);

        $query = new AvailableSlotsByParticipantQuery($this->event->reveal(), $this->participant->reveal());
        $handler = new AvailableSlotsByParticipantQueryHandler(
            $this->dDayGuesser->reveal(),
            $this->meetingSlotRepository->reveal(),
            $dateTime
        );
        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }

    public function testHandle()
    {
        $dateTime = new \DateTime('2017-08-12 12:00:00.000');
        $begin1 = new \DateTime('2017-08-12 12:00:00.000');
        $end1   = new \DateTime('2017-08-12 12:15:00.000');
        $begin2 = new \DateTime('2017-08-12 11:00:00.000');
        $end2   = new \DateTime('2017-08-12 11:15:00.000');
        $begin3 = new \DateTime('2017-08-12 13:00:00.000');
        $end3   = new \DateTime('2017-08-12 13:15:00.000');
        $begin4 = new \DateTime('2017-08-12 14:00:00.000');
        $end4   = new \DateTime('2017-08-12 14:15:00.000');
        $begin5 = new \DateTime('2017-08-13 09:00:00.000');
        $end5   = new \DateTime('2017-08-13 09:15:00.000');

        $slotAvailableView3 = new AvailableSlotView(3, $begin3, $end3);
        $slotAvailableView4 = new AvailableSlotView(4, $begin4, $end4);
        $slotAvailableView5 = new AvailableSlotView(5, $begin5, $end5);
        $expected = [
            $slotAvailableView3,
            $slotAvailableView4,
            $slotAvailableView5,
        ];
        $slot1 = new MeetingSlot($this->event->reveal(), $begin1, $end1, false);
        $slot2 = new MeetingSlot($this->event->reveal(), $begin2, $end2, false);
        $slot3 = new MeetingSlot($this->event->reveal(), $begin3, $end3, false);
        $slot4 = new MeetingSlot($this->event->reveal(), $begin4, $end4, false);
        $slot5 = new MeetingSlot($this->event->reveal(), $begin5, $end5, false);

        $userReflection = new \ReflectionClass(MeetingSlot::class);
        $usedIdProperty   = $userReflection->getProperty('id');
        $usedIdProperty->setAccessible(true);
        $usedIdProperty->setValue($slot1, 1);
        $usedIdProperty->setValue($slot2, 2);
        $usedIdProperty->setValue($slot3, 3);
        $usedIdProperty->setValue($slot4, 4);
        $usedIdProperty->setValue($slot5, 5);

        $this->dDayGuesser->isItDDayAndFeatureEnabled($this->event->reveal())->shouldBeCalled()->willReturn(true);
        $this->meetingSlotRepository
            ->findAvailableSlotsByParticipants($this->event->reveal(), [$this->participant->reveal()])
            ->shouldBeCalled()
            ->willReturn([
                $slot1,
                $slot2,
                $slot3,
                $slot4,
                $slot5,
            ]);

        $query = new AvailableSlotsByParticipantQuery($this->event->reveal(), $this->participant->reveal());
        $handler = new AvailableSlotsByParticipantQueryHandler(
            $this->dDayGuesser->reveal(),
            $this->meetingSlotRepository->reveal(),
            $dateTime
        );
        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }
}
