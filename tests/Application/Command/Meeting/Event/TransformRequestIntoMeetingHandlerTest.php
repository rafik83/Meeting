<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Event;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeeting;
use Proximum\Vimeet\Application\Command\Meeting\Event\TransformRequestIntoMeetingHandler;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class TransformRequestIntoMeetingHandlerTest extends TestCase
{
    public function testHandleWithAssignedParticipantsOnBothSide()
    {
        $fromSheet       = $this->prophesize(Sheet::class);
        $toSheet         = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $toParticipant   = $this->prophesize(Participant::class);
        $request         = $this->prophesize(Request::class);

        $request->getFromParticipantsArray()->willReturn([$fromParticipant->reveal()]);
        $request->getToParticipantsArray()->willReturn([$toParticipant->reveal()]);
        $request->hasNoPreference($fromSheet)->willReturn(false);
        $request->hasNoPreference($toSheet)->willReturn(false);

        $meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);

        $transformRequestIntoMeetingHandler = new TransformRequestIntoMeetingHandler(
            $meetingSlotRepository->reveal()
        );

        $query = new TransformRequestIntoMeeting($request->reveal());

        $transformRequestIntoMeetingHandler->handle($query);
    }
}
