<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteMeetingHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;
use Proximum\Vimeet\Tests\Factory\SlotFactory;
use Proximum\Vimeet\Tests\Factory\SpotFactory;
use Proximum\Vimeet\Tests\Factory\UserFactory;
use PHPUnit\Framework\TestCase;

class DeleteMeetingHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Data
        $event     = EventFactory::createEvent();
        $user      = UserFactory::create('toto@tata.fr');
        $user2     = UserFactory::create('titi@tutu.fr');
        $fromSheet = SheetFactory::create($event, $user);
        $toSheet   = SheetFactory::create($event, $user2);
        $slot      = SlotFactory::createSlot(1, $event);
        $spot      = SpotFactory::create($event);
        $request   = new Meeting\Request($fromSheet, [], $toSheet, [], new \DateTime(), $user, $event);
        $meeting   = new Meeting($request, $slot, $fromSheet, [], $toSheet, [], new \DateTime(), $spot, $event);

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcher::class);

        $meetingRepository->remove($meeting)->shouldBeCalled();
        $eventDispatcher
            ->dispatch(
                Events::MEETING_REMOVED,
                new MeetingRemovedEvent(
                    [
                        $fromSheet,
                        $toSheet,
                    ]
                )
            )
            ->shouldBeCalled()
        ;

        // Handler
        $handler = new DeleteMeetingHandler($meetingRepository->reveal(), $eventDispatcher->reveal());
        $handler->handle(new DeleteMeeting($meeting));
    }
}
