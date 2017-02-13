<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use DateTime;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlotHandler;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UpdateSlotHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $fromUser        = UserFactory::create();
        $fromSheet       = SheetFactory::create($event, $fromUser);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = SheetFactory::create($event, $toUser);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser);

        $slot1 = SlotFactory::createSlot(67, $event);
        $slot2 = SlotFactory::createSlot(76, $event);

        $spot1 = SpotFactory::create($event, 'Spot 1');
        $spot2 = SpotFactory::create($event, 'Spot 2');

        $meeting = new Meeting(
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            null,
            null,
            false
        )->shouldBeCalled()->willReturn([$spot2]);

        $expectedMeeting = new Meeting(
            $request,
            $slot2,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot2,
            false,
            false
        );

        $updateSlot = new UpdateSlot($meeting, $slot2, false);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal()
        );

        $meetingRepository->set($expectedMeeting)->shouldBeCalled();

        $updateSpotHandler->handle($updateSlot);
    }

    public function testMeetingIsBlockedSlotException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $fromUser        = UserFactory::create();
        $fromSheet       = SheetFactory::create($event, $fromUser);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = SheetFactory::create($event, $toUser);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser);
        $slot1           = SlotFactory::createSlot(67, $event);
        $slot2           = SlotFactory::createSlot(76, $event);
        $spot1           = SpotFactory::create($event, 'Spot 1');

        $meeting = new Meeting(
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            false,
            true
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $updateSlot = new UpdateSlot($meeting, $slot2);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal()
        );

        $this->expectException(MeetingIsBlockedSlotException::class);
        $updateSpotHandler->handle($updateSlot);
    }

    public function testSlotNotAvailableForThisMeetingException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $fromUser        = UserFactory::create();
        $fromSheet       = SheetFactory::create($event, $fromUser);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = SheetFactory::create($event, $toUser);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser);

        $slot1 = SlotFactory::createSlot(67, $event);
        $slot2 = SlotFactory::createSlot(404, $event);
        $spot1 = SpotFactory::create($event, 'Spot 1');

        $meeting = new Meeting(
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting))
            ->shouldBeCalled()
            // not returned 404 ($slot2)
            ->willReturn(new MeetingUpdateSlotView([67]));

        $updateSlot = new UpdateSlot($meeting, $slot2);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->expectException(SlotNotAvailableForThisMeetingException::class);
        $updateSpotHandler->handle($updateSlot);
    }

    public function testNoSpotsAvailableForThisSlotAndMeetingException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $fromUser        = UserFactory::create();
        $fromSheet       = SheetFactory::create($event, $fromUser);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = SheetFactory::create($event, $toUser);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser);

        $slot1 = SlotFactory::createSlot(67, $event);
        $slot2 = SlotFactory::createSlot(76, $event);
        $spot1 = SpotFactory::create($event, 'Spot 1');

        $meeting = new Meeting(
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        // No spots are available for selected slot
        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            null,
            null,
            false
        )->shouldBeCalled()->willReturn([]);

        $updateSlot = new UpdateSlot($meeting, $slot2);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->expectException(NoSpotsAvailableForThisSlotAndMeetingException::class);

        $updateSpotHandler->handle($updateSlot);
    }

    public function testBlockedSpotNotAvailableForThisMeetingAndSlotException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $fromUser        = UserFactory::create();
        $fromSheet       = SheetFactory::create($event, $fromUser);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = SheetFactory::create($event, $toUser);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser);

        $slot1 = SlotFactory::createSlot(67, $event);
        $slot2 = SlotFactory::createSlot(76, $event);

        $spot1 = SpotFactory::create($event, 'Spot 1');
        $spot2 = SpotFactory::create($event, 'Spot 2');

        $meeting = new Meeting(
            $request,
            $slot1,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            true,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            null,
            null,
            false
        )->shouldBeCalled()->willReturn([$spot2]);

        $updateSlot = new UpdateSlot($meeting, $slot2);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->expectException(BlockedSpotNotAvailableForThisMeetingAndSlotException::class);

        $updateSpotHandler->handle($updateSlot);
    }
}
