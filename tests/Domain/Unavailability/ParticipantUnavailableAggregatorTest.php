<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Unavailability\ParticipantUnavailableAggregator;

class ParticipantUnavailableAggregatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAggregateWithNoSlot()
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getId()->willReturn(123);
        $sheet->getEvent()->willReturn($event->reveal());

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findAvailableSlotsByParticipantsIds($event->reveal(), [123])->shouldBeCalled()->willReturn([]);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participant->setFullyUnavailable(true)->shouldBeCalled();

        $participantUnavailableAggregator = new ParticipantUnavailableAggregator(
            $meetingSlotRepository->reveal(),
            $participantRepository->reveal()
        );
        $participantUnavailableAggregator->aggregateUnavailability($participant->reveal());
    }

    public function testAggregateWithSlots()
    {
        $event = $this->prophesize(Event::class);
        $sheet = $this->prophesize(Sheet::class);
        $slot  = $this->prophesize(MeetingSlot::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getId()->willReturn(123);
        $sheet->getEvent()->willReturn($event->reveal());

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->findAvailableSlotsByParticipantsIds($event->reveal(), [123])->shouldBeCalled()->willReturn([$slot->reveal()]);

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository->set($participant->reveal())->shouldBeCalled();

        $participant->setFullyUnavailable(false)->shouldBeCalled();

        $participantUnavailableAggregator = new ParticipantUnavailableAggregator(
            $meetingSlotRepository->reveal(),
            $participantRepository->reveal()
        );
        $participantUnavailableAggregator->aggregateUnavailability($participant->reveal());
    }
}
