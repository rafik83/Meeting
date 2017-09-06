<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQuery;
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Model\Event\Day;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;

class AvailableSlotsByParticipantQueryHandlerTest extends TestCase
{
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

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $slot1 = SlotFactory::createSlot(1, $event, $slotBegin1, $slotEnd1);
        $slot2 = SlotFactory::createSlot(2, $event, $slotBegin2, $slotEnd2);

        $availableSlots = [$slot1, $slot2];
        $expected = [new AvailableSlotView(2, $slot2->getBegin(), $slot2->getEnd())];
        $query    = new AvailableSlotsByParticipantQuery($event, $participant, $day);
        $handler  = new AvailableSlotsByParticipantQueryHandler($meetingSlotRepository->reveal(), $currentTime);

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldBeCalled()
            ->willReturn($availableSlots);

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


        $sheet       = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $slot1 = SlotFactory::createSlot(1, $event, $slotBegin1, $slotEnd1);
        $slot2 = SlotFactory::createSlot(2, $event, $slotBegin2, $slotEnd2);

        $availableSlots = [$slot1, $slot2];
        $expected = [];

        $query   = new AvailableSlotsByParticipantQuery($event, $participant, $day);
        $handler = new AvailableSlotsByParticipantQueryHandler($meetingSlotRepository->reveal(), $currentTime);

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldBeCalled()
            ->willReturn($availableSlots);

        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }
}
