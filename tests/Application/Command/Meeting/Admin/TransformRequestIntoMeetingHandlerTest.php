<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event          = EventFactory::createEvent();
        $meetingRequest = $this->prophesize(Request::class);
        $slot           = $this->prophesize(MeetingSlot::class);
        $spot           = SpotFactory::create();
        $fromSheet      = SheetFactory::create();
        $toSheet        = SheetFactory::create();
        $isVisio        = false;

        // Mock
        $meetingRepository           = $this->prophesize(MeetingRepositoryInterface::class);
        $requestSlotViewQueryHandler = $this->prophesize(RequestSlotViewQueryHandler::class);
        $availableSpots              = $this->prophesize(AvailableSpots::class);
        $datetime                    = new \DateTime();
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);
        $meetParticipants = $this->prophesize(MeetingParticipants::class);

        $meetParticipants->getMeetingParticipants($meetingRequest->reveal(), $fromSheet)->willReturn([]);
        $meetParticipants->getMeetingParticipants($meetingRequest->reveal(), $toSheet)->willReturn([]);
        $meetParticipants->countAllMeetingParticipants($meetingRequest->reveal())->willReturn(10);

        $meetingRequest->isTransformableIntoMeeting()->willReturn(true);
        $meetingRequest->getFromSheet()->willReturn($fromSheet);
        $meetingRequest->getToSheet()->willReturn($toSheet);

        $slot->getId()->willReturn(1);
        $slot->getEvent()->willReturn($event);

        $requestSlotViewQueryHandler->handle(
            new RequestSlotViewQuery(
                $meetingRequest->reveal(),
                $isVisio
            )
        )->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([1]));

        $availableSpots->getBySlot(
            $slot->reveal(),
            $fromSheet,
            $toSheet,
            10,
            $isVisio
        )->shouldBeCalled()->willReturn($spot);

        // Expected meeting
        $meeting = new Meeting(
            $meetingRequest->reveal(),
            $slot->reveal(),
            $fromSheet,
            [],
            $toSheet,
            [],
            $datetime,
            $spot,
            $event,
            false,
            false,
            Meeting::CREATED_BY_ADMIN
        );

        $meetingRepository->add($meeting)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent($meeting)
        )->shouldBeCalled();

        $query = new TransformRequestIntoMeeting(
            $meetingRequest->reveal(),
            $slot->reveal(),
            $isVisio
        );

        $handler = new TransformRequestIntoMeetingHandler(
            $meetingRepository->reveal(),
            $requestSlotViewQueryHandler->reveal(),
            $availableSpots->reveal(),
            $datetime,
            $eventDispatcher->reveal(),
            $meetParticipants->reveal()
        );

        $handler->handle($query);
    }
}
