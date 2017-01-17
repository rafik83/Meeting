<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\MeetingSlot\Remove;
use Proximum\Vimeet\Application\Command\MeetingSlot\RemoveHandler;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class RemoveHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $meetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->checkMeetingFromSlot($meetingSlot)->shouldBeCalled()->willReturn(1);
        $meetingSlotRepository->remove($meetingSlot)->shouldBeCalled();

        $handler = new RemoveHandler(
            $meetingSlotRepository->reveal(),
            $meetingRepository->reveal()
        );
        $handler->handle(new Remove($meetingSlot));
    }

    /**
     * @expectedException        \Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException
     */
    public function testIsNotAllowedToRemoveSlotException()
    {
        $event = EventFactory::createEvent();
        $begin = new \DateTime();
        $end   = new \DateTime();

        $meetingSlot = new MeetingSlot($event, $begin, $end, false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->checkMeetingFromSlot($meetingSlot)->shouldBeCalled()->willReturn(null);

        $handler = new RemoveHandler(
            $meetingSlotRepository->reveal(),
            $meetingRepository->reveal()
        );
        $handler->handle(new Remove($meetingSlot));
    }
}
