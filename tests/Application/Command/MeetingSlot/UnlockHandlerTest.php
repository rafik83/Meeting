<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Unlock;
use Proximum\Vimeet\Application\Command\MeetingSlot\UnlockHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UnlockHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $lockedMeetingSlot   = new MeetingSlot($event, $begin, $end, true);
        $unlockedMeetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingSlotRepository->set($unlockedMeetingSlot)->shouldBeCalled();

        $handler = new UnlockHandler($meetingSlotRepository->reveal());
        $handler->handle(new Unlock($lockedMeetingSlot));
    }
}
