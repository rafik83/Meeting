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
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantWithPhoneValidated;
use Proximum\Vimeet\Domain\Slot\SlotPlus10minutes;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    /** @var Event */
    public $event;

    /** @var \DateTime */
    public $datetime;

    /** @var Sheet */
    public $fromSheet;

    /** @var Sheet */
    public $toSheet;

    /** @var Participant */
    public $fromParticipant;

    /** @var Participant */
    public $toParticipant;

    /** @var Request */
    public $request;

    /** @var MeetingSlot */
    public $slot;

    /** @var Spot */
    public $spot;

    public function setUp()
    {
        $this->event    = EventFactory::createEvent();
        $this->datetime = new \DateTime();

        $this->fromSheet       = $this->prophesize(Sheet::class);
        $this->toSheet         = $this->prophesize(Sheet::class);
        $this->fromParticipant = $this->prophesize(Participant::class);
        $this->toParticipant   = $this->prophesize(Participant::class);
        $this->request         = $this->prophesize(Request::class);
        $this->slot            = $this->prophesize(MeetingSlot::class);
        $this->spot            = $this->prophesize(Spot::class);
    }

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
        $slotPlus10Minutes             = $this->prophesize(SlotPlus10minutes::class);

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

        $slotPlus10Minutes->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalled()->willReturn([$slot->reveal()]);

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
            $slotPlus10Minutes->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testHandleWithFromSheetHasNoPreference()
    {
        $slotTwo = $this->prophesize(MeetingSlot::class);
        $slotTwo->getId()->willReturn(2);
        $fromParticipantTwo = $this->prophesize(Participant::class);
        $fromParticipantTwo->getId()->willReturn(3);

        $this->request->getEvent()->willReturn($this->event);
        $this->request->getFromParticipantsArray()->willReturn([$this->fromParticipant->reveal()]);
        $this->request->getToParticipantsArray()->willReturn([$this->toParticipant->reveal()]);
        $this->request->hasNoPreference($this->fromSheet)->willReturn(true);
        $this->request->hasNoPreference($this->toSheet)->willReturn(false);
        $this->request->getFromSheet()->willReturn($this->fromSheet);
        $this->request->getToSheet()->willReturn($this->toSheet);

        $this->slot->getId()->willReturn(1);
        $this->fromParticipant->getId()->willReturn(1);
        $this->toParticipant->getId()->willReturn(2);

        $this->fromSheet->getParticipantsArray()
            ->willReturn([
                $this->fromParticipant->reveal(),
                $fromParticipantTwo->reveal()
            ]);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(EventDispatcherInterface::class);
        $slotPlus10Minutes             = $this->prophesize(SlotPlus10minutes::class);

        // Expected

        $expectedMeeting = new Meeting(
            $this->request->reveal(),
            $this->slot->reveal(),
            $this->fromSheet->reveal(),
            [$this->fromParticipant->reveal()],
            $this->toSheet->reveal(),
            [$this->toParticipant->reveal()],
            $this->datetime,
            $this->spot->reveal(),
            $this->event
        );

        // Mock

        // from sheet slot by participant
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $this->event,
            [$this->fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$this->slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $this->event,
            [$fromParticipantTwo->reveal()]
        )->shouldBeCalled()->willReturn([$slotTwo->reveal()]);

        // to sheet slot for all selected participants
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $this->event,
            [$this->toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$this->slot->reveal()]);

        $slotPlus10Minutes->getFilteredSlots([$this->slot->reveal()])
            ->shouldBeCalled()->willReturn([$this->slot->reveal()]);
        $slotPlus10Minutes->getFilteredSlots([$slotTwo->reveal()])
            ->shouldBeCalled()->willReturn([$slotTwo->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $this->event,
            $this->fromSheet->reveal(),
            [$this->fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn($this->fromParticipant->reveal());

        $participantWithPhoneValidated->getParticipant(
            $this->event,
            $this->toSheet->reveal(),
            [$this->toParticipant->reveal()]
        )->shouldBeCalled()->willReturn($this->toParticipant->reveal());

        $availableSpots->getBySlot(
            $this->slot->reveal(),
            $this->fromSheet->reveal(),
            $this->toSheet->reveal(),
            2,
            false
        )->shouldBeCalled()->willReturn($this->spot->reveal());

        $meetingRepository->add($expectedMeeting)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$this->fromSheet->reveal(), $this->toSheet->reveal()])
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
            $slotPlus10Minutes->reveal(),
            $eventDispatcher->reveal(),
            $this->datetime
        );

        $query = new TransformRequestIntoMeeting($this->request->reveal(), false);

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }
}
