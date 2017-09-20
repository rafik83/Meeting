<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\RequestSlotViewQueryHandler;
use Proximum\Vimeet\Application\View\Agenda\Admin\MeetingUpdateSlotView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
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
        $spotRepository              = $this->prophesize(SpotRepositoryInterface::class);
        $requestSlotViewQueryHandler = $this->prophesize(RequestSlotViewQueryHandler::class);
        $datetime                    = new \DateTime();
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);

        $meetingRequest->isTransformableIntoMeeting()->willReturn(true);
        $meetingRequest->countParticipants()->willReturn(10);
        $meetingRequest->getFromSheet()->willReturn($fromSheet);
        $meetingRequest->getToSheet()->willReturn($toSheet);
        $meetingRequest->getParticipants($fromSheet)->willReturn([]);
        $meetingRequest->getParticipants($toSheet)->willReturn([]);

        $slot->getId()->willReturn(1);
        $slot->getEvent()->willReturn($event);

        $requestSlotViewQueryHandler->handle(
            new RequestSlotViewQuery(
                $meetingRequest->reveal(),
                $isVisio
            )
        )->shouldBeCalled()
            ->willReturn(new MeetingUpdateSlotView([1]));

        $spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $slot->reveal(),
            10,
            null,
            $fromSheet,
            $toSheet,
            $isVisio
        )->shouldBeCalled()
            ->willReturn([$spot]);

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
            $event
        );

        $meetingRepository->add($meeting)->shouldBeCalled();

        $eventDispatcher->dispatch(
            Events::MEETING_CREATED,
            new MeetingCreatedEvent([$fromSheet, $toSheet])
        )->shouldBeCalled();

        $query = new TransformRequestIntoMeeting(
            $meetingRequest->reveal(),
            $slot->reveal(),
            $isVisio,
            false
        );

        $handler = new TransformRequestIntoMeetingHandler(
            $meetingRepository->reveal(),
            $spotRepository->reveal(),
            $requestSlotViewQueryHandler->reveal(),
            $datetime,
            $eventDispatcher->reveal()
        );

        $handler->handle($query);
    }
}
