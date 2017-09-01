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
use Proximum\Vimeet\Application\Query\Agenda\AvailableSheets\AvailableSlotsByParticipantsQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Slot\AvailableSlotView;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;

class AvailableSlotsByParticipantQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $sheet = SheetFactory::create($event);
        $participant = ParticipantFactory::create($sheet);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $availableSlots = [
            SlotFactory::createSlot(1),
            SlotFactory::createSlot(2),
        ];

        $expected = [
            new AvailableSlotView(1),
            new AvailableSlotView(2),
        ];

        $query = new AvailableSlotsByParticipantQuery($event, $participant);
        $handler = new AvailableSlotsByParticipantsQueryHandler($meetingSlotRepository->reveal());

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$participant])
            ->shouldBeCalled()
            ->willReturn($availableSlots);

        $result = $handler->handle($query);

        $this->assertEquals($expected, $result);
    }
}
