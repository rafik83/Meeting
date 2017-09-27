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
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingParticipateEvent;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\CannotBeTransformIntoMeetingOnDdayException;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Request\ParticipantWithPhoneValidated;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Tests\Factory\EventFactory;

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
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter             = $this->prophesize(SlotFilter::class);

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

        $slotFilter->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipant->reveal()]
        )->shouldNotBeCalled();

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$toParticipant->reveal()]
        )->shouldNotBeCalled();

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
            $slotFilter->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testHandleWithFromSheetHasNoPreference()
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

        $slotTwo = $this->prophesize(MeetingSlot::class);
        $slotTwo->getId()->willReturn(2);
        $fromParticipantTwo = $this->prophesize(Participant::class);
        $fromParticipantTwo->getId()->willReturn(3);

        $request->getEvent()->willReturn($event);
        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet)->willReturn(true);
        $request->hasNoPreference($toSheet)->willReturn(false);
        $request->getFromSheet()->willReturn($fromSheet);
        $request->getToSheet()->willReturn($toSheet);

        $slot->getId()->willReturn(1);
        $fromParticipant->getId()->willReturn(1);
        $toParticipant->getId()->willReturn(2);

        $fromSheet->countParticipants()->willReturn(2);
        $toSheet->countParticipants()->willReturn(1);

        $fromSheet->getParticipantsArray()
            ->willReturn([
                $fromParticipant->reveal(),
                $fromParticipantTwo->reveal(),
            ]);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter             = $this->prophesize(SlotFilter::class);

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

        // from sheet slot by participant
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipantTwo->reveal()]
        )->shouldBeCalled()->willReturn([$slotTwo->reveal()]);

        // to sheet slot for all selected participants
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $slotFilter->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalled()->willReturn([$slot->reveal()]);

        $slotFilter->getFilteredSlots([$slotTwo->reveal()])
            ->shouldBeCalled()->willReturn([$slotTwo->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn($fromParticipant->reveal());

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$toParticipant->reveal()]
        )->shouldNotBeCalled();

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
            $slotFilter->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testNoSpotAvailable()
    {
        $this->expectException(CannotBeTransformIntoMeetingOnDdayException::class);

        $datetime = new \DateTime();
        $event = EventFactory::createEvent();

        $fromSheet       = $this->prophesize(Sheet::class);
        $toSheet         = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant   = $this->prophesize(Participant::class);
        $request         = $this->prophesize(Request::class);
        $slot            = $this->prophesize(MeetingSlot::class);

        $request->getEvent()->willReturn($event);
        $request->getFromSheet()->willReturn($fromSheet);
        $request->getToSheet()->willReturn($toSheet);
        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet->reveal())->willReturn(false);
        $request->hasNoPreference($toSheet->reveal())->willReturn(false);

        $slot->getId()->willReturn(1);
        $fromParticipant->getId()->willReturn(1);
        $toParticipant->getId()->willReturn(2);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter             = $this->prophesize(SlotFilter::class);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $slotFilter->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipant->reveal()]
        )->shouldNotBeCalled();

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$toParticipant->reveal()]
        )->shouldNotBeCalled();

        $availableSpots->getBySlot(
            $slot->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            2,
            false
        )->shouldBeCalled()
            ->willThrow(NoSpotsAvailableForThisSlotAndMeetingException::class);

        $meetingRepository->add(Argument::type(Meeting::class))->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$fromSheet->reveal(), $toSheet->reveal()])
        )->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            Argument::type(MeetingParticipateEvent::class)
        )->shouldNotBeCalled();

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $participantWithPhoneValidated->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $transformRequestIntoMeetingHandler->handle($query);
    }

    public function testNoSlotInCommon()
    {
        $this->expectException(CannotBeTransformIntoMeetingOnDdayException::class);

        $datetime = new \DateTime();
        $event = EventFactory::createEvent();

        $fromSheet       = $this->prophesize(Sheet::class);
        $toSheet         = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant   = $this->prophesize(Participant::class);
        $request         = $this->prophesize(Request::class);
        $slot            = $this->prophesize(MeetingSlot::class);
        $otherSlot       = $this->prophesize(MeetingSlot::class);

        $request->getEvent()->willReturn($event);
        $request->getFromSheet()->willReturn($fromSheet);
        $request->getToSheet()->willReturn($toSheet);
        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet->reveal())->willReturn(false);
        $request->hasNoPreference($toSheet->reveal())->willReturn(false);

        $slot->getId()->willReturn(1);
        $otherSlot->getId()->willReturn(9);
        $fromParticipant->getId()->willReturn(1);
        $toParticipant->getId()->willReturn(2);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter             = $this->prophesize(SlotFilter::class);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $slotFilter->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$otherSlot->reveal()]);

        $slotFilter->getFilteredSlots([$otherSlot->reveal()])
            ->shouldBeCalled()->willReturn([$otherSlot->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipant->reveal()]
        )->shouldNotBeCalled();

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$toParticipant->reveal()]
        )->shouldNotBeCalled();

        $availableSpots->getBySlot(
            $slot->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            2,
            false
        )->shouldNotBeCalled();

        $meetingRepository->add(Argument::type(Meeting::class))->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$fromSheet->reveal(), $toSheet->reveal()])
        )->shouldNotBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            Argument::type(MeetingParticipateEvent::class)
        )->shouldNotBeCalled();

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $participantWithPhoneValidated->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $transformRequestIntoMeetingHandler->handle($query);
    }

    public function testPriorityForParticipantWithPhoneValidated()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $fromSheet          = $this->prophesize(Sheet::class);
        $toSheet            = $this->prophesize(Sheet::class);
        $fromParticipant    = $this->prophesize(Participant::class);
        $fromParticipantTwo = $this->prophesize(Participant::class);
        $toParticipant      = $this->prophesize(Participant::class);
        $toParticipantTwo   = $this->prophesize(Participant::class);
        $request            = $this->prophesize(Request::class);
        $slot               = $this->prophesize(MeetingSlot::class);
        $spot               = $this->prophesize(Spot::class);

        $request->getEvent()->willReturn($event);
        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet)->willReturn(true);
        $request->hasNoPreference($toSheet)->willReturn(true);
        $request->getFromSheet()->willReturn($fromSheet);
        $request->getToSheet()->willReturn($toSheet);

        $slot->getId()->willReturn(1);
        $fromParticipant->getId()->willReturn(1);
        $fromParticipantTwo->getId()->willReturn(3);
        $toParticipant->getId()->willReturn(2);
        $toParticipantTwo->getId()->willReturn(4);

        $fromSheet->countParticipants()->willReturn(2);
        $fromSheet->getParticipantsArray()->willReturn([$fromParticipant->reveal(), $fromParticipantTwo->reveal()]);
        $toSheet->countParticipants()->willReturn(2);
        $toSheet->getParticipantsArray()->willReturn([
            $toParticipant->reveal(),
            $toParticipantTwo->reveal(),
        ]);

        $meetingSlotRepository         = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $participantWithPhoneValidated = $this->prophesize(ParticipantWithPhoneValidated::class);
        $availableSpots                = $this->prophesize(AvailableSpots::class);
        $meetingRepository             = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher               = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter             = $this->prophesize(SlotFilter::class);

        // Expected

        $expectedMeeting = new Meeting(
            $request->reveal(),
            $slot->reveal(),
            $fromSheet->reveal(),
            [$fromParticipantTwo->reveal()],
            $toSheet->reveal(),
            [$toParticipant->reveal()],
            $datetime,
            $spot->reveal(),
            $event
        );

        // Mock

        // Find available slots for each fromSheet participant because no preference
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$fromParticipantTwo->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        // Find available slots for each toSheet participant because no preference
        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $meetingSlotRepository->findAvailableSlotsByParticipants(
            $event,
            [$toParticipantTwo->reveal()]
        )->shouldBeCalled()->willReturn([$slot->reveal()]);

        $slotFilter->getFilteredSlots([$slot->reveal()])
            ->shouldBeCalledTimes(4)->willReturn([$slot->reveal()]);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipant->reveal()]
        )->shouldBeCalled()->willReturn(null);

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$fromParticipantTwo->reveal()]
        )->shouldBeCalled()->willReturn($fromParticipantTwo->reveal());

        $participantWithPhoneValidated->getParticipant(
            $event,
            [$toParticipant->reveal()]
        )->shouldBeCalled()->willReturn(null);

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
            $slotFilter->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal(), false);

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }
}
