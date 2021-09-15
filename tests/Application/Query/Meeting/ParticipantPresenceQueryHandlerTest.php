<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantPresenceQuery;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantPresenceQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantPresenceView;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class ParticipantPresenceQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getId()->willReturn(1);
        $participant2 = $this->prophesize(Participant::class);
        $participant2->getId()->willReturn(2);

        $meetingSlot = $this->prophesize(MeetingSlot::class);
        $meeting = $this->prophesize(Meeting::class);
        $meeting->getFromParticipants()->willReturn(new ArrayCollection([$participant1->reveal()]));
        $meeting->getToParticipants()->willReturn(new ArrayCollection([$participant2->reveal()]));

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant1->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $isParticipantVisio->isSatisfiedBy($participant2->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $isParticipantPresentToMeeting = $this->prophesize(IsParticipantPresentToMeeting::class);
        $isParticipantPresentToMeeting->isSatisfiedBy($participant1->reveal(), $meeting->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $isParticipantPresentToMeeting->isSatisfiedBy($participant2->reveal(), $meeting->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $meetingRepository->findByMeetingSlot($meetingSlot->reveal())
            ->shouldBeCalled()
            ->willReturn([$meeting]);

        $expectedResult = [
            2 => new ParticipantPresenceView(2, false),
            1 => new ParticipantPresenceView(1, true)
        ];

        $handler = new ParticipantPresenceQueryHandler($isParticipantPresentToMeeting->reveal(), $meetingRepository->reveal(), $isParticipantVisio->reveal());
        $result  = $handler->handle(new ParticipantPresenceQuery($meetingSlot->reveal()));

        $this->assertEquals($result, $expectedResult);
    }
}
