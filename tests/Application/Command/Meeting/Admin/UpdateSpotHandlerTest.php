<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpot;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSpotHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedSpotEvent;
use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\ParticipantFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;

class UpdateSpotHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $fromUser        = UserFactory::create();
        $fromSheet       = new Sheet($event, $type, [], $fromUser, $dateTime);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);
        $slot            = new MeetingSlot($event, new \DateTime(), new \DateTime(), false);

        $spot1 = SpotFactory::create($event, 'Spot 1');
        $spot2 = SpotFactory::create($event, 'Spot 2');

        $meeting = new Meeting(
            $request,
            $slot,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            $event,
            false,
            false
        );

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $expectedMeeting = new Meeting(
            $request,
            $slot,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot2,
            $event,
            true,
            true
        );

        $meetingRepository->set($expectedMeeting)->shouldBeCalled();

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $meeting->getSlot(),
            $meeting->countParticipants(),
            $meeting,
            null,
            null,
            false
        )->shouldBeCalled()->willReturn([$spot1, $spot2]);

        $eventDispatcher->dispatch(Events::MEETING_MOVED_SPOT, new MeetingMovedSpotEvent($meeting));

        // Change Spot and block slot and spot
        $updateSpot        = new UpdateSpot($meeting, $spot2, true, true);
        $updateSpotHandler = new UpdateSpotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $updateSpotHandler->handle($updateSpot);
    }

    public function testMeetingIsBlockedSpotException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $fromUser        = UserFactory::create();
        $fromSheet       = new Sheet($event, $type, [], $fromUser, $dateTime);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);
        $slot            = new MeetingSlot($event, new \DateTime(), new \DateTime(), false);

        $spot1 = SpotFactory::create($event, 'Spot 1');
        $spot2 = SpotFactory::create($event, 'Spot 2');

        $meeting = new Meeting(
            $request,
            $slot,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            $event,
            true,
            false
        );

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();
        $spotRepository->getSpotsForSlotAndParticipantsQuantity()->shouldNotBeCalled();

        $this->expectException(MeetingIsBlockedSpotException::class);

        $updateSpot        = new UpdateSpot($meeting, $spot2, true, true);
        $updateSpotHandler = new UpdateSpotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $updateSpotHandler->handle($updateSpot);
    }

    public function testSpotNotAvailableForThisMeetingException()
    {
        $dateTime        = new DateTime();
        $event           = EventFactory::createEvent();
        $type            = new Type($event);
        $fromUser        = UserFactory::create();
        $fromSheet       = new Sheet($event, $type, [], $fromUser, $dateTime);
        $fromParticipant = ParticipantFactory::create($fromSheet, $fromUser);
        $toUser          = UserFactory::create();
        $toSheet         = new Sheet($event, $type, [], $toUser, $dateTime);
        $toParticipant   = ParticipantFactory::create($toSheet, $toUser);
        $request         = new Request($fromSheet, [], $toSheet, [], $dateTime, $fromUser, $event);
        $slot            = new MeetingSlot($event, new \DateTime(), new \DateTime(), false);

        $spot1 = SpotFactory::create($event, 'Spot 1');
        $spot2 = SpotFactory::create($event, 'Spot 2');

        $meeting = new Meeting(
            $request,
            $slot,
            $fromSheet,
            [$fromParticipant],
            $toSheet,
            [$toParticipant],
            $dateTime,
            $spot1,
            $event,
            false,
            false
        );

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $spotRepository = $this->prophesize(SpotRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $meeting->getSlot(),
            $meeting->countParticipants(),
            $meeting,
            null,
            null,
            false
        )->shouldBeCalled()->willReturn([$spot1]); // $spot2 not returned

        $meetingRepository->set(Argument::any())->shouldNotBeCalled();

        $this->expectException(SpotNotAvailableForThisMeetingException::class);

        // Change Spot, select one not available
        $updateSpot        = new UpdateSpot($meeting, $spot2, false, false);
        $updateSpotHandler = new UpdateSpotHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $eventDispatcher->reveal()
        );
        $updateSpotHandler->handle($updateSpot);
    }
}
