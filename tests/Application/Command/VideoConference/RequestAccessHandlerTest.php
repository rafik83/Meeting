<?php

namespace Proximum\Vimeet\Tests\Application\Command\VideoConference;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Command\VideoConference\RequestAccess;
use Proximum\Vimeet\Application\Command\VideoConference\RequestAccessHandler;
use Proximum\Vimeet\Application\Components\Visio\VisioSettingsRetriever;
use Proximum\Vimeet\Application\View\Meeting\VideoConferenceView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\VideoConference;
use Proximum\Vimeet\Domain\Model\VideoConferenceToken;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\VideoConferenceRepositoryInterface;

class RequestAccessHandlerTest extends TestCase
{
    public function testHandleWithoutExistingVideoConference(): void
    {
        $slotEnd = new \DateTime();
        $meeting = $this->prophesize(Meeting::class);
        $slot    = $this->prophesize(MeetingSlot::class);
        $user    = $this->prophesize(User::class);

        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $slot->getEnd()->shouldBeCalled()->willReturn($slotEnd);
        $meeting->getSlot()->shouldBeCalled()->willReturn($slot->reveal());
        $meeting->hasParticipant($participant->reveal())->shouldBeCalled()->willReturn(false);
        $meeting->addParticipant($participant->reveal())->shouldBeCalled();

        // Mock
        $session                   = $this->prophesize(Session::class);
        $videoConferenceAdapter    = $this->prophesize(VideoConferenceAdapterInterface::class);
        $videoConferenceRepository = $this->prophesize(VideoConferenceRepositoryInterface::class);

        $session->getSessionId()->shouldBeCalled()->willReturn('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9');
        $videoConferenceAdapter->getApiKey()->shouldBeCalled()->willReturn('API_KEY');

        $event = $this->prophesize(Event::class);
        $meeting->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $visioSettings = $this->prophesize(VisioSettings::class);
        $visioSettings->getHeader('fr')->shouldBeCalled()->willReturn('header.png');
        $visioSettings->getEndSound('fr')->shouldBeCalled()->willReturn('sound.png');
        $visioSettings->getEndImage('fr')->shouldBeCalled()->willReturn('end_image.png');
        $visioSettings->getEndMessage('fr')->shouldBeCalled()->willReturn('message');
        $visioSettingsRetriever = $this->prophesize(VisioSettingsRetriever::class);
        $visioSettingsRetriever->get($event->reveal())->shouldBeCalled()->willReturn($visioSettings->reveal());

        $videoConferenceRepository->findByMeeting($meeting->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $videoConferenceAdapter->createSession()
            ->shouldBeCalled()
            ->willReturn($session->reveal())
        ;

        $videoConferenceAdapter->generateAccessToken($session->reveal(), $slotEnd)
            ->shouldBeCalled()
            ->willReturn('TOKEN')
        ;

        $videoConference = new VideoConference('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9', $meeting->reveal());
        $videoConference->setToken(new VideoConferenceToken($videoConference, $user->reveal(), 'TOKEN'));

        $videoConferenceRepository->add($videoConference)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->set($meeting->reveal())->shouldBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->isAvailableForMeeting([$participant->reveal()], $meeting->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $handler = new RequestAccessHandler(
            $meetingRepository->reveal(),
            $participantRepository->reveal(),
            $videoConferenceAdapter->reveal(),
            $videoConferenceRepository->reveal(),
            $visioSettingsRetriever->reveal()
        );

        // Expected
        $expectedVideoConferenceView = new VideoConferenceView(
            'TOKEN',
            'T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9',
            'API_KEY',
            'header.png',
            'sound.png',
            'end_image.png',
            'message'
        );

        $videoConferenceView = $handler->handle(
            new RequestAccess(
                $meeting->reveal(),
                $participant->reveal(),
                'fr'
            )
        );

        $this->assertEquals($expectedVideoConferenceView, $videoConferenceView);
    }

    public function testHandleWithExistingVideoConference(): void
    {
        $slotEnd = new \DateTime();
        $meeting = $this->prophesize(Meeting::class);
        $slot    = $this->prophesize(MeetingSlot::class);
        $user    = $this->prophesize(User::class);

        $sheet = $this->prophesize(Sheet::class);
        $participant = $this->prophesize(Participant::class);
        $participant->getSheet()->shouldBeCalled()->willReturn($sheet->reveal());
        $participant->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $slot->getEnd()->shouldBeCalled()->willReturn($slotEnd);
        $meeting->getSlot()->shouldBeCalled()->willReturn($slot->reveal());
        $meeting->hasParticipant($participant->reveal())->shouldBeCalled()->willReturn(true);

        // Mock
        $videoConference           = $this->prophesize(VideoConference::class);
        $session                   = $this->prophesize(Session::class);
        $videoConferenceInterface  = $this->prophesize(VideoConferenceAdapterInterface::class);
        $videoConferenceRepository = $this->prophesize(VideoConferenceRepositoryInterface::class);

        $videoConference->getTokenByUser($user->reveal())->willReturn(null);
        $videoConference->getSessionId()->willReturn('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9');
        $videoConferenceInterface->getApiKey()->shouldBeCalled()->willReturn('API_KEY');

        $event = $this->prophesize(Event::class);
        $meeting->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $visioSettings = $this->prophesize(VisioSettings::class);
        $visioSettings->getHeader('fr')->shouldBeCalled()->willReturn('header.png');
        $visioSettings->getEndSound('fr')->shouldBeCalled()->willReturn('sound.png');
        $visioSettings->getEndImage('fr')->shouldBeCalled()->willReturn('end_image.png');
        $visioSettings->getEndMessage('fr')->shouldBeCalled()->willReturn('message');
        $visioSettingsRetriever = $this->prophesize(VisioSettingsRetriever::class);
        $visioSettingsRetriever->get($event->reveal())->shouldBeCalled()->willReturn($visioSettings->reveal());

        $videoConferenceRepository->findByMeeting($meeting->reveal())
            ->shouldBeCalled()
            ->willReturn($videoConference->reveal())
        ;

        $videoConferenceInterface->getSession('T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9')
            ->shouldBeCalled()
            ->willReturn($session->reveal())
        ;

        $videoConferenceInterface->generateAccessToken($session->reveal(), $slotEnd)
            ->shouldBeCalled()
            ->willReturn('TOKEN')
        ;

        $videoConference->setToken(
            new VideoConferenceToken(
                $videoConference->reveal(),
                $user->reveal(),
                'TOKEN'
            )
        )->shouldBeCalled()
        ;

        $videoConferenceRepository->set($videoConference)->shouldBeCalled();

        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $meetingRepository->set($meeting->reveal())->shouldNotBeCalled();

        $participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $participantRepository
            ->isAvailableForMeeting([$participant->reveal()], $meeting->reveal())
            ->shouldNotBeCalled()
        ;

        $handler = new RequestAccessHandler(
            $meetingRepository->reveal(),
            $participantRepository->reveal(),
            $videoConferenceInterface->reveal(),
            $videoConferenceRepository->reveal(),
            $visioSettingsRetriever->reveal()
        );

        // Expected
        $expectedVideoConferenceView = new VideoConferenceView(
            'TOKEN',
            'T1==cGFydG5lcl9pZD00NTkyNjE2MiZzaWc9',
            'API_KEY',
            'header.png',
            'sound.png',
            'end_image.png',
            'message'
        );

        $videoConferenceView = $handler->handle(
            new RequestAccess(
                $meeting->reveal(),
                $participant->reveal(),
                'fr'
            )
        );

        $this->assertEquals($expectedVideoConferenceView, $videoConferenceView);
    }
}
