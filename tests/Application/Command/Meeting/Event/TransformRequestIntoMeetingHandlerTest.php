<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    public function testHandleWithAssignedParticipantsOnBothSide()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $fromSheet       = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $fromSheet->getParticipantsArray()->willReturn([$fromParticipant->reveal()]);

        $toSheet       = $this->prophesize(Sheet::class);
        $toParticipant = $this->prophesize(Participant::class);
        $toSheet->getParticipantsArray()->willReturn([$toParticipant->reveal()]);

        $request = $this->prophesize(Request::class);
        $slot    = $this->prophesize(MeetingSlot::class);
        $spot    = $this->prophesize(Spot::class);

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

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $availableSpots        = $this->prophesize(AvailableSpots::class);
        $meetingRepository     = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter            = $this->prophesize(SlotFilter::class);
        $visioGuesser          = $this->prophesize(VisioGuesser::class);

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

        $expectedMeeting->setCreatedByParticipant();

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
            new MeetingCreatedEvent($expectedMeeting)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            Argument::type(MeetingParticipateEvent::class)
        )->shouldBeCalledTimes(2);

        $visioGuesser
            ->isParticipantVisio([$fromParticipant->reveal(), $toParticipant->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $visioGuesser->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal());

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testHandleWithFromSheetHasNoPreferenceWithOneParticipant()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $fromSheet       = $this->prophesize(Sheet::class);
        $fromUser        = $this->prophesize(User::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $fromSheet->getParticipantsArray()->willReturn([$fromParticipant->reveal()]);

        $toSheet       = $this->prophesize(Sheet::class);
        $toUser        = $this->prophesize(User::class);
        $toParticipant = $this->prophesize(Participant::class);
        $toSheet->getParticipantsArray()->willReturn([$toParticipant->reveal()]);

        $request = $this->prophesize(Request::class);
        $slot    = $this->prophesize(MeetingSlot::class);
        $spot    = $this->prophesize(Spot::class);

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
        $fromParticipant->getUser()->willReturn($fromUser->reveal());
        $toParticipant->getId()->willReturn(2);
        $toParticipant->getUser()->willReturn($toUser->reveal());

        $fromSheet->countParticipants()->willReturn(2);
        $toSheet->countParticipants()->willReturn(1);

        $fromSheet->getParticipantsArray()
            ->willReturn([
                $fromParticipant->reveal(),
                $fromParticipantTwo->reveal(),
            ]);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);
        $availableSpots        = $this->prophesize(AvailableSpots::class);
        $meetingRepository     = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher       = $this->prophesize(DelayedEventDispatcherInterface::class);
        $slotFilter            = $this->prophesize(SlotFilter::class);
        $visioGuesser          = $this->prophesize(VisioGuesser::class);

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

        $availableSpots->getBySlot(
            $slot->reveal(),
            $fromSheet->reveal(),
            $toSheet->reveal(),
            2,
            false
        )->shouldBeCalled()->willReturn($spot->reveal());

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

        $expectedMeeting->setCreatedByParticipant();

        $meetingRepository->add($expectedMeeting)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent($expectedMeeting)
        )->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_PARTICIPATE,
            Argument::type(MeetingParticipateEvent::class)
        )->shouldBeCalledTimes(2);

        $visioGuesser
            ->isParticipantVisio([$fromParticipant->reveal(), $toParticipant->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $visioGuesser->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal());

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testHandleWithBothSideHasNoPreference()
    {
        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot1->getId()->willReturn(111);

        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot2->getId()->willReturn(222);

        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot3->getId()->willReturn(333);

        $spot = $this->prophesize(Spot::class);

        $userFromParticipant2 = $this->prophesize(User::class);
        $userToParticipant1 = $this->prophesize(User::class);
        $userToParticipant2 = $this->prophesize(User::class);

        $fromParticipant1 = $this->prophesize(Participant::class);
        $fromParticipant1->getId()->willReturn(11);

        $fromParticipant2 = $this->prophesize(Participant::class);
        $fromParticipant2->getId()->willReturn(12);
        $fromParticipant2->getUser()->willReturn($userFromParticipant2);

        $fromParticipant3 = $this->prophesize(Participant::class);
        $fromParticipant3->getId()->willReturn(13);

        $toParticipant1 = $this->prophesize(Participant::class);
        $toParticipant1->getId()->willReturn(21);
        $toParticipant1->getUser()->willReturn($userToParticipant1);

        $toParticipant2 = $this->prophesize(Participant::class);
        $toParticipant2->getId()->willReturn(22);
        $toParticipant2->getUser()->willReturn($userToParticipant2);

        $fromSheet = $this->prophesize(Sheet::class);
        $fromSheet->getParticipantsArray()->willReturn(
            [$fromParticipant1->reveal(), $fromParticipant2->reveal(), $fromParticipant3->reveal()]
        );
        $fromSheet->countParticipants()->willReturn(3);

        $toSheet = $this->prophesize(Sheet::class);
        $toSheet->getParticipantsArray()->willReturn(
            [$toParticipant1->reveal(), $toParticipant2->reveal()]
        );
        $toSheet->countParticipants()->willReturn(2);

        $request = $this->prophesize(Request::class);
        $request->getFromSheet()->willreturn($fromSheet->reveal());
        $request->getToSheet()->willreturn($toSheet->reveal());
        $request->getEvent()->willreturn($event);
        $request->getFromParticipantsArray()->willReturn([]);
        $request->getToParticipantsArray()->willReturn([]);
        $request->hasNoPreference($fromSheet->reveal())->willReturn(true);
        $request->hasNoPreference($toSheet->reveal())->willReturn(true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal(), $slot2->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal(), $slot3->reveal()])
        ;

        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);

        $userEventPhoneChecker
            ->isValidated(
                $userFromParticipant2->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $userEventPhoneChecker
            ->isValidated(
                $userToParticipant1->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $userEventPhoneChecker
            ->isValidated(
                $userToParticipant2->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $availableSpots = $this->prophesize(AvailableSpots::class);
        $availableSpots
            ->getBySlot(
                $slot2->reveal(),
                $fromSheet->reveal(),
                $toSheet->reveal(),
                2,
                false
            )
            ->shouldBeCalled()
            ->willReturn($spot->reveal())
        ;

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcherInterface::class);

        $slotFilter = $this->prophesize(SlotFilter::class);
        $slotFilter->getFilteredSlots([])->willReturn([]);
        $slotFilter->getFilteredSlots([$slot1->reveal()])->willReturn([$slot1->reveal()]);
        $slotFilter->getFilteredSlots([$slot1->reveal(), $slot2->reveal()])->willReturn(
            [$slot1->reveal(), $slot2->reveal()]
        );
        $slotFilter->getFilteredSlots([$slot2->reveal()])->willReturn([$slot2->reveal()]);
        $slotFilter->getFilteredSlots([$slot2->reveal(), $slot3->reveal()])->willReturn(
            [$slot2->reveal(), $slot3->reveal()]
        );

        $visioGuesser = $this->prophesize(VisioGuesser::class);

        $visioGuesser
            ->isParticipantVisio([$fromParticipant2->reveal(), $toParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $visioGuesser
            ->isParticipantVisio([$fromParticipant2->reveal(), $toParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $visioGuesser->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $query = new TransformRequestIntoMeeting($request->reveal());

        $meeting = $transformRequestIntoMeetingHandler->handle($query);

        $expectedMeeting = new Meeting(
            $request->reveal(),
            $slot2->reveal(),
            $fromSheet->reveal(),
            [$fromParticipant2->reveal()],
            $toSheet->reveal(),
            [$toParticipant2->reveal()],
            $datetime,
            $spot->reveal(),
            $event
        );

        $expectedMeeting->setCreatedByParticipant();

        $this->assertEquals($expectedMeeting, $meeting);
    }

    public function testNoSpotAvailable()
    {
        $this->expectException(CannotBeTransformIntoMeetingOnDdayException::class);

        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot1->getId()->willReturn(111);

        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot2->getId()->willReturn(222);

        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot3->getId()->willReturn(333);

        $userFromParticipant2 = $this->prophesize(User::class);
        $userToParticipant1 = $this->prophesize(User::class);
        $userToParticipant2 = $this->prophesize(User::class);

        $fromParticipant1 = $this->prophesize(Participant::class);
        $fromParticipant1->getId()->willReturn(11);

        $fromParticipant2 = $this->prophesize(Participant::class);
        $fromParticipant2->getId()->willReturn(12);
        $fromParticipant2->getUser()->willReturn($userFromParticipant2);

        $fromParticipant3 = $this->prophesize(Participant::class);
        $fromParticipant3->getId()->willReturn(13);

        $toParticipant1 = $this->prophesize(Participant::class);
        $toParticipant1->getId()->willReturn(21);
        $toParticipant1->getUser()->willReturn($userToParticipant1);

        $toParticipant2 = $this->prophesize(Participant::class);
        $toParticipant2->getId()->willReturn(22);
        $toParticipant2->getUser()->willReturn($userToParticipant2);

        $fromSheet = $this->prophesize(Sheet::class);
        $fromSheet->getParticipantsArray()->willReturn(
            [$fromParticipant1->reveal(), $fromParticipant2->reveal(), $fromParticipant3->reveal()]
        );
        $fromSheet->countParticipants()->willReturn(3);

        $toSheet = $this->prophesize(Sheet::class);
        $toSheet->getParticipantsArray()->willReturn(
            [$toParticipant1->reveal(), $toParticipant2->reveal()]
        );
        $toSheet->countParticipants()->willReturn(2);

        $request = $this->prophesize(Request::class);
        $request->getFromSheet()->willreturn($fromSheet->reveal());
        $request->getToSheet()->willreturn($toSheet->reveal());
        $request->getEvent()->willreturn($event);
        $request->getFromParticipantsArray()->willReturn([]);
        $request->getToParticipantsArray()->willReturn([]);
        $request->hasNoPreference($fromSheet->reveal())->willReturn(true);
        $request->hasNoPreference($toSheet->reveal())->willReturn(true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal(), $slot2->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal(), $slot3->reveal()])
        ;

        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);

        $userEventPhoneChecker
            ->isValidated(
                $userFromParticipant2->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $userEventPhoneChecker
            ->isValidated(
                $userToParticipant1->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $userEventPhoneChecker
            ->isValidated(
                $userToParticipant2->reveal(),
                $event
            )
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $availableSpots = $this->prophesize(AvailableSpots::class);
        $availableSpots
            ->getBySlot(
                $slot2->reveal(),
                $fromSheet->reveal(),
                $toSheet->reveal(),
                2,
                false
            )
            ->shouldBeCalled()
            ->willThrow(NoSpotsAvailableForThisSlotAndMeetingException::class)
        ;

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcherInterface::class);

        $slotFilter = $this->prophesize(SlotFilter::class);
        $slotFilter->getFilteredSlots([])->willReturn([]);
        $slotFilter->getFilteredSlots([$slot1->reveal()])->willReturn([$slot1->reveal()]);
        $slotFilter->getFilteredSlots([$slot1->reveal(), $slot2->reveal()])->willReturn(
            [$slot1->reveal(), $slot2->reveal()]
        );
        $slotFilter->getFilteredSlots([$slot2->reveal()])->willReturn([$slot2->reveal()]);
        $slotFilter->getFilteredSlots([$slot2->reveal(), $slot3->reveal()])->willReturn(
            [$slot2->reveal(), $slot3->reveal()]
        );

        $visioGuesser = $this->prophesize(VisioGuesser::class);

        $visioGuesser
            ->isParticipantVisio([$fromParticipant2->reveal(), $toParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $visioGuesser
            ->isParticipantVisio([$fromParticipant2->reveal(), $toParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $visioGuesser->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $transformRequestIntoMeetingHandler->handle(new TransformRequestIntoMeeting($request->reveal()));
    }

    public function testNoSlotInCommon()
    {
        $this->expectException(CannotBeTransformIntoMeetingOnDdayException::class);

        $event    = EventFactory::createEvent();
        $datetime = new \DateTime();

        $slot1 = $this->prophesize(MeetingSlot::class);
        $slot1->getId()->willReturn(111);

        $slot2 = $this->prophesize(MeetingSlot::class);
        $slot2->getId()->willReturn(222);

        $slot3 = $this->prophesize(MeetingSlot::class);
        $slot3->getId()->willReturn(333);

        $userFromParticipant2 = $this->prophesize(User::class);
        $userToParticipant1 = $this->prophesize(User::class);
        $userToParticipant2 = $this->prophesize(User::class);

        $fromParticipant1 = $this->prophesize(Participant::class);
        $fromParticipant1->getId()->willReturn(11);

        $fromParticipant2 = $this->prophesize(Participant::class);
        $fromParticipant2->getId()->willReturn(12);
        $fromParticipant2->getUser()->willReturn($userFromParticipant2);

        $fromParticipant3 = $this->prophesize(Participant::class);
        $fromParticipant3->getId()->willReturn(13);

        $toParticipant1 = $this->prophesize(Participant::class);
        $toParticipant1->getId()->willReturn(21);
        $toParticipant1->getUser()->willReturn($userToParticipant1);

        $toParticipant2 = $this->prophesize(Participant::class);
        $toParticipant2->getId()->willReturn(22);
        $toParticipant2->getUser()->willReturn($userToParticipant2);

        $fromSheet = $this->prophesize(Sheet::class);
        $fromSheet->getParticipantsArray()->willReturn(
            [$fromParticipant1->reveal(), $fromParticipant2->reveal(), $fromParticipant3->reveal()]
        );
        $fromSheet->countParticipants()->willReturn(3);

        $toSheet = $this->prophesize(Sheet::class);
        $toSheet->getParticipantsArray()->willReturn(
            [$toParticipant1->reveal(), $toParticipant2->reveal()]
        );
        $toSheet->countParticipants()->willReturn(2);

        $request = $this->prophesize(Request::class);
        $request->getFromSheet()->willreturn($fromSheet->reveal());
        $request->getToSheet()->willreturn($toSheet->reveal());
        $request->getEvent()->willreturn($event);
        $request->getFromParticipantsArray()->willReturn([]);
        $request->getToParticipantsArray()->willReturn([]);
        $request->hasNoPreference($fromSheet->reveal())->willReturn(true);
        $request->hasNoPreference($toSheet->reveal())->willReturn(true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$fromParticipant3->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot1->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant1->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal()])
        ;

        $meetingSlotRepository
            ->findAvailableSlotsByParticipants($event, [$toParticipant2->reveal()])
            ->shouldBeCalled()
            ->willReturn([$slot2->reveal(), $slot3->reveal()])
        ;

        $userEventPhoneChecker = $this->prophesize(UserEventPhoneChecker::class);

        $availableSpots = $this->prophesize(AvailableSpots::class);
        $availableSpots->getBySlot()->shouldNotBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher   = $this->prophesize(DelayedEventDispatcherInterface::class);

        $slotFilter = $this->prophesize(SlotFilter::class);
        $slotFilter->getFilteredSlots([])->willReturn([]);
        $slotFilter->getFilteredSlots([$slot1->reveal()])->willReturn([$slot1->reveal()]);
        $slotFilter->getFilteredSlots([$slot1->reveal(), $slot2->reveal()])->willReturn(
            [$slot1->reveal(), $slot2->reveal()]
        );
        $slotFilter->getFilteredSlots([$slot2->reveal()])->willReturn([$slot2->reveal()]);
        $slotFilter->getFilteredSlots([$slot2->reveal(), $slot3->reveal()])->willReturn(
            [$slot2->reveal(), $slot3->reveal()]
        );

        $visioGuesser = $this->prophesize(VisioGuesser::class);

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal(),
            $userEventPhoneChecker->reveal(),
            $availableSpots->reveal(),
            $meetingRepository->reveal(),
            $slotFilter->reveal(),
            $visioGuesser->reveal(),
            $eventDispatcher->reveal(),
            $datetime
        );

        $transformRequestIntoMeetingHandler->handle(new TransformRequestIntoMeeting($request->reveal()));
    }
}
