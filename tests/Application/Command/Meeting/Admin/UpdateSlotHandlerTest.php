<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlotHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Exception\Meeting\BlockedSpotNotAvailableForThisMeetingAndSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSlotException;
use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Application\Exception\Meeting\SlotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UpdateSlotHandlerTest extends TestCase
{
    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var Event */
    private $event;

    /** @var User */
    private $fromUser;

    /** @var Sheet */
    private $fromSheet;

    /** @var Participant */
    private $fromParticipant;

    /** @var User */
    private $toUser;

    /** @var Sheet */
    private $toSheet;

    /** @var Participant */
    private $toParticipant;

    /** @var Request */
    private $request;

    /** @var ObjectProphecy */
    private $eventDispatcher;

    public function setUp()
    {
        $this->dateTime        = new DateTime();
        $this->event           = EventFactory::createEvent();
        $this->fromUser        = UserFactory::create();
        $this->fromSheet       = SheetFactory::create($this->event, $this->fromUser);
        $this->fromParticipant = ParticipantFactory::create($this->fromSheet, $this->fromUser);
        $this->toUser          = UserFactory::create();
        $this->toSheet         = SheetFactory::create($this->event, $this->toUser);
        $this->toParticipant   = ParticipantFactory::create($this->toSheet, $this->toUser);
        $this->request         = new Request($this->fromSheet, [], $this->toSheet, [], $this->dateTime, $this->fromUser, $this->event);
        $this->eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
    }

    public function testHandle()
    {
        $slot1 = SlotFactory::createSlot(67, $this->event);
        $slot2 = SlotFactory::createSlot(76, $this->event);

        $spot1 = SpotFactory::create($this->event, 'Spot 1');
        $spot2 = SpotFactory::create($this->event, 'Spot 2');

        $meeting = new Meeting(
            $this->request,
            $slot1,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot1,
            $this->event,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting, false))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            $this->fromSheet,
            $this->toSheet,
            false
        )->shouldBeCalled()->willReturn([$spot2]);

        $expectedMeeting = new Meeting(
            $this->request,
            $slot2,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot2,
            $this->event,
            false,
            false
        );

        $updateSlot = new UpdateSlot($meeting, $slot2, false);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal(),
            $this->eventDispatcher->reveal()
        );

        $meetingRepository->set($expectedMeeting)->shouldBeCalled();
        $this->eventDispatcher->dispatch(Events::MEETING_MOVED, new MeetingMovedEvent($expectedMeeting))->shouldBeCalled();

        $updateSpotHandler->handle($updateSlot);
    }

    public function testMeetingIsBlockedSlotException()
    {
        $slot1           = SlotFactory::createSlot(67, $this->event);
        $slot2           = SlotFactory::createSlot(76, $this->event);
        $spot1           = SpotFactory::create($this->event, 'Spot 1');

        $meeting = new Meeting(
            $this->request,
            $slot1,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot1,
            $this->event,
            false,
            true
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $updateSlot = new UpdateSlot($meeting, $slot2, false);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal(),
            $this->eventDispatcher->reveal()
        );

        $this->expectException(MeetingIsBlockedSlotException::class);
        $updateSpotHandler->handle($updateSlot);
    }

    public function testSlotNotAvailableForThisMeetingException()
    {
        $slot1 = SlotFactory::createSlot(67, $this->event);
        $slot2 = SlotFactory::createSlot(404, $this->event);
        $spot1 = SpotFactory::create($this->event, 'Spot 1');

        $meeting = new Meeting(
            $this->request,
            $slot1,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot1,
            $this->event,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting, false))
            ->shouldBeCalled()
            // not returned 404 ($slot2)
            ->willReturn(new MeetingUpdateSlotView([67]));

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $updateSlot = new UpdateSlot($meeting, $slot2, false);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal(),
            $this->eventDispatcher->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->expectException(SlotNotAvailableForThisMeetingException::class);
        $updateSpotHandler->handle($updateSlot);
    }

    public function testNoSpotsAvailableForThisSlotAndMeetingException()
    {
        $slot1 = SlotFactory::createSlot(67, $this->event);
        $slot2 = SlotFactory::createSlot(76, $this->event);
        $spot1 = SpotFactory::create($this->event, 'Spot 1');

        $meeting = new Meeting(
            $this->request,
            $slot1,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot1,
            $this->event,
            false,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting, true))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        // No spots are available for selected slot
        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            $this->fromSheet,
            $this->toSheet,
            true
        )->shouldBeCalled()->willReturn([]);

        $updateSlot = new UpdateSlot($meeting, $slot2, true);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal(),
            $this->eventDispatcher->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->expectException(NoSpotsAvailableForThisSlotAndMeetingException::class);

        $updateSpotHandler->handle($updateSlot);
    }

    public function testBlockedSpotNotAvailableForThisMeetingAndSlotException()
    {
        $slot1 = SlotFactory::createSlot(67, $this->event);
        $slot2 = SlotFactory::createSlot(76, $this->event);

        $spot1 = SpotFactory::create($this->event, 'Spot 1');
        $spot2 = SpotFactory::create($this->event, 'Spot 2');

        $meeting = new Meeting(
            $this->request,
            $slot1,
            $this->fromSheet,
            [$this->fromParticipant],
            $this->toSheet,
            [$this->toParticipant],
            $this->dateTime,
            $spot1,
            $this->event,
            true,
            false
        );

        $meetingRepository                 = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository                    = $this->prophesize(SpotRepositoryInterface::class);
        $meetingUpdateSlotViewQueryHandler = $this->prophesize(MeetingUpdateSlotViewQueryHandler::class);

        $meetingUpdateSlotViewQueryHandler
            ->handle(new MeetingUpdateSlotViewQuery($meeting, true))
            ->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([67, 76]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot2,
            2,
            $meeting,
            $this->fromSheet,
            $this->toSheet,
            true
        )->shouldBeCalled()->willReturn([$spot2]);

        $this->eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $updateSlot = new UpdateSlot($meeting, $slot2, true);
        $updateSpotHandler = new UpdateSlotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $meetingUpdateSlotViewQueryHandler->reveal(),
            $this->eventDispatcher->reveal()
        );

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $this->expectException(BlockedSpotNotAvailableForThisMeetingAndSlotException::class);

        $updateSpotHandler->handle($updateSlot);
    }
}
