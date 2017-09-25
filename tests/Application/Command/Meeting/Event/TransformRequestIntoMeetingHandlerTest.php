<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantWithPhoneValidated;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    public function testHandleWithAssignedParticipantsOnBothSide()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $fromSheet       = $this->prophesize(Sheet::class);
        $toSheet         = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant   = $this->prophesize(Participant::class);
        $request         = $this->prophesize(Request::class);
        $slot            = $this->prophesize(MeetingSlot::class);
        $spot            = $this->prophesize(Spot::class);

        $request->getEvent()->willReturn($event);
        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet)->willReturn(false);
        $request->hasNoPreference($toSheet)->willReturn(false);
        $request->getFromSheet()->willReturn($fromSheet);
        $request->getToSheet()->willReturn($toSheet);

        $slot->getId()->willReturn(1);
        $fromParticipant->getId()->willReturn(1);
        $toParticipant->getId()->willReturn(2);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(EventDispatcherInterface::class);

        // Expected

        $expectedMeeting = new Meeting(
            $request->reveal(),
            $slot->reveal(),
            $fromSheet->reveal(),
            [$fromParticipant->reveal()],
            $toSheet->reveal(),
            [$toParticipant->reveal()],
            $datetime,
            $spot->reveal(),
            $event
        );

        // Mock

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            $fromSheet->reveal(),
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn($fromParticipant->reveal());

        $participantWithPhoneValidated->getParticipant(
            $event,
            $toSheet->reveal(),
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn($toParticipant->reveal());

        $availableSpots->getBySlot(
            $slot->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            2,
            false
        )->shouldBeCalled()->willReturn($spot->reveal());

        $meetingRepository->add($expectedMeeting)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$fromSheet->reveal(), $toSheet->reveal()])
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            Argument::type(MeetingParticipateEvent::class)
        )->shouldBeCalledTimes(2);

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $participantWithPhoneValidated->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $transformRequestIntoMeetingHandler->handle($query);
    }
}
