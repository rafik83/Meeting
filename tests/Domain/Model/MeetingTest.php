<?php

namespace Proximum\Vimeet\Tests\Domain\Model;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User;

class MeetingTest extends TestCase
{
    public function testHasNotUserInLinkedSheets()
    {
        $event = $this->prophesize(Event::class);
        $meetingRequest = $this->prophesize(Meeting\Request::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $spot = $this->prophesize(Spot::class);
        $fromSheet = $this->prophesize(Sheet::class);
        $fromParticipant = $this->prophesize(Participant::class);
        $toSheet = $this->prophesize(Sheet::class);
        $toParticipant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);

        $meeting = new Meeting(
            $meetingRequest->reveal(),
            $slot->reveal(),
            $fromSheet->reveal(),
            [$fromParticipant->reveal()],
            $toSheet->reveal(),
            [$toParticipant->reveal()],
            new \DateTime(),
            $spot->reveal(),
            $event->reveal(),
            false,
            false,
            Meeting::CREATED_BY_PLANNER
        );

        $fromSheet->hasUser($user->reveal())->shouldBeCalled()->willReturn(false);
        $toSheet->hasUser($user->reveal())->shouldBeCalled()->willReturn(false);
        $fromSheet->hasUserInLinkedSheets($user->reveal())->shouldBeCalled()->willReturn(false);
        $toSheet->hasUserInLinkedSheets($user->reveal())->shouldBeCalled()->willReturn(true);

        $this->assertEquals($toSheet->reveal(), $meeting->getSheetOfUser($user->reveal()));
    }
}
