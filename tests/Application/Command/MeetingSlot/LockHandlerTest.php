<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Lock;
use Proximum\Vimeet\Application\Command\MeetingSlot\LockHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class LockHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $unlockedMeetingSlot = new MeetingSlot($event, $begin, $end, false);
        $lockedMeetingSlot   = new MeetingSlot($event, $begin, $end, true);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->set($lockedMeetingSlot)->shouldBeCalled();

        $handler = new LockHandler($meetingSlotRepository->reveal());
        $handler->handle(new Lock($unlockedMeetingSlot));
    }
}
