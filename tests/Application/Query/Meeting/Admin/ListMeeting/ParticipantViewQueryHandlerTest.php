<?php

namespace Proximum\Vimeet\Tests\Application\Query\Meeting\Admin\ListMeeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQuery;
use Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting\ParticipantViewQueryHandler;
use Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting\ParticipantView;
use Proximum\Vimeet\Domain\Meeting\IsParticipantPresentToMeeting;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $participant = $this->prophesize(Participant::class);
        $user = $this->prophesize(User::class);
        $slot = $this->prophesize(MeetingSlot::class);
        $event = $this->prophesize(Event::class);
        $meeting = $this->prophesize(Meeting::class);
        $isParticipantPresentToMeeting = $this->prophesize(IsParticipantPresentToMeeting::class);

        $participant->getId()->shouldBeCalled()->willReturn(12);
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());
        $locale = 'fr';

        $isParticipantPresentToMeeting->isSatisfiedBy($participant->reveal(), $meeting->reveal())->shouldBeCalled()->willReturn(false);

        $scanRepository = $this->prophesize(ScanRepositoryInterface::class);
        $scanRepository->isUserCheckinByEventAndSlot($user->reveal(), $event->reveal(), $slot->reveal())
            ->shouldBeCalled()
            ->willReturn(true);
        $participantInfoGuesser = $this->prophesize(ParticipantInfoGuesser::class);
        $participantInfoGuesser->guessParticipantCompleteName($participant->reveal(), $locale)
            ->shouldBeCalled()
            ->willReturn('Jean Paul')
        ;

        $query = new ParticipantViewQuery($participant->reveal(), $event->reveal(), $meeting->reveal(), $slot->reveal(), $locale);

        $isParticipantVisio = $this->prophesize(IsParticipantVisio::class);
        $isParticipantVisio->isSatisfiedBy($participant->reveal())->willReturn(false);

        $handler = new ParticipantViewQueryHandler(
            $participantInfoGuesser->reveal(),
            $scanRepository->reveal(),
            $isParticipantPresentToMeeting->reveal(),
            $isParticipantVisio->reveal()
        );

        $result = $handler->handle($query);

        $expected = new ParticipantView(12, 'Jean Paul', true);

        $this->assertEquals($expected, $result);
    }
}
