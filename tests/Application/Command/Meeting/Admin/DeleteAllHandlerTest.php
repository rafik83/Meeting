<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAll;
use Proximum\Vimeet\Application\Command\Meeting\Admin\DeleteAllHandler;
use Proximum\Vimeet\Application\Exception\Meeting\NotAllowedToDeleteAllMeetingsException;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DeleteAllHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithException()
    {
        $this->expectException(NotAllowedToDeleteAllMeetingsException::class);

        // Data
        $event = EventFactory::createEvent();

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->deleteAll($event)->shouldNotBeCalled();

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(true);

        // Handler
        $handler = new DeleteAllHandler($meetingRepository->reveal(), $meetingPublishedAccessChecker->reveal());
        $handler->handle(new DeleteAll($event));
    }

    public function testHandle()
    {
        // Data
        $event = EventFactory::createEvent();

        // Mock
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->deleteAll($event)->shouldBeCalled();

        $meetingPublishedAccessChecker = $this->prophesize(MeetingPublishedAccessChecker::class);
        $meetingPublishedAccessChecker->allowedToAccess($event)->shouldBeCalled()->willReturn(false);

        // Handler
        $handler = new DeleteAllHandler($meetingRepository->reveal(), $meetingPublishedAccessChecker->reveal());
        $handler->handle(new DeleteAll($event));
    }
}
