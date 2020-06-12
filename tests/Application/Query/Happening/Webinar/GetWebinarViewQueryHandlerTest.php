<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQueryHandler;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfos;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfosHandler;
use Proximum\Vimeet\Application\Query\User\Event\Participant\ParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Application\View\Happening\WebinarView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class GetWebinarViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|GetUserParticipantInfosHandler */
    private $getUserParticipantInfosHandler;

    /** @var ObjectProphecy|VideoConferenceAdapterInterface */
    private $videoConferenceAdapter;

    /** @var GetWebinarViewQueryHandler */
    private $getWebinarViewQueryHandler;

    /** @var \DateTime */
    private $dateTime;

    protected function setUp(): void
    {
        $this->getUserParticipantInfosHandler = $this->prophesize(GetUserParticipantInfosHandler::class);
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->dateTime = new \DateTime('2020-03-30 12:00:00');

        $this->getWebinarViewQueryHandler = new GetWebinarViewQueryHandler(
            $this->getUserParticipantInfosHandler->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->dateTime
        );
    }

    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(111);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn($user1->reveal());
        $speaker2->getUser()->willReturn($user2->reveal());
        $user1->getId()->willReturn(1);
        $user2->getId()->willReturn(2);
        $speaker1->getFirstname()->willReturn('Jeanne');
        $speaker2->getFirstname()->willReturn('John');
        $speaker1->getLastname()->willReturn('Dupont');
        $speaker2->getLastname()->willReturn('Doe');
        $speaker1->getPosition('en')->willReturn('Développeuse');
        $speaker2->getPosition('en')->willReturn('Ingénieur');
        $speaker1->getOrganization()->willReturn('Fairness');
        $speaker2->getOrganization()->willReturn('Proximum');

        $speakers = [
            $speaker1,
            $speaker2,
        ];

        $happening = $this->prophesize(Happening::class);
        $happening->getTitle('en')->shouldBeCalled()->willReturn(
            'Webinar: how to work remotely during the Covid-19 crisis'
        );
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(true);
        $happening->getSpeakers()->shouldBeCalled()->willReturn($speakers);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getEvent()->shouldNotBeCalled();

        $session = $this->prophesize(Session::class);
        $session->getSessionId()->shouldBeCalled()->willReturn('webinar-session-id');

        $this->videoConferenceAdapter->getSession('webinar-session-id')->shouldBeCalled()->willReturn(
            $session->reveal()
        );
        $this->videoConferenceAdapter->getApiKey()->shouldBeCalled()->willReturn('api key');

        $this->videoConferenceAdapter->generateAccessToken(
            $session->reveal(),
            new \DateTime('2020-03-30 12:15:00'),
            [],
            true
        )->shouldBeCalled()->willReturn('User token');

        $speakerViews = [
            new WebinarSpeakerView(
                1,
                'Jeanne',
                'Dupont',
                'Développeuse',
                'Fairness'
            ),
            new WebinarSpeakerView(
                2,
                'John',
                'Doe',
                'Ingénieur',
                'Proximum'
            ),
        ];

        $this->assertEquals(
            new WebinarView(
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                'User token',
                'webinar-session-id',
                'api key',
                true,
                $speakerViews,
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                180,
                '/path/image.jpg'
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }

    public function testHandleInteractiveWebinar(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(111);

        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn($user1->reveal());
        $speaker2->getUser()->willReturn($user2->reveal());
        $user1->getId()->willReturn(1);
        $user2->getId()->willReturn(2);
        $speaker1->getFirstname()->willReturn('Jeanne');
        $speaker2->getFirstname()->willReturn('John');
        $speaker1->getLastname()->willReturn('Dupont');
        $speaker2->getLastname()->willReturn('Doe');
        $speaker1->getPosition('en')->willReturn('Développeuse');
        $speaker2->getPosition('en')->willReturn('Ingénieur');
        $speaker1->getOrganization()->willReturn('Fairness');
        $speaker2->getOrganization()->willReturn('Proximum');

        $speakers = [
            $speaker1,
            $speaker2,
        ];

        $event = $this->prophesize(Event::class);
        $happening = $this->prophesize(Happening::class);
        $happening->getTitle('en')->shouldBeCalled()->willReturn(
            'Webinar: how to work remotely during the Covid-19 crisis'
        );
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(true);
        $happening->hasSpeaker($user->reveal())->shouldNotBeCalled();
        $happening->getSpeakers()->shouldBeCalled()->willReturn($speakers);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $session = $this->prophesize(Session::class);
        $session->getSessionId()->shouldBeCalled()->willReturn('webinar-session-id');

        $this->videoConferenceAdapter->getSession('webinar-session-id')->shouldBeCalled()->willReturn(
            $session->reveal()
        );
        $this->videoConferenceAdapter->getApiKey()->shouldBeCalled()->willReturn('api key');

        $this->videoConferenceAdapter->generateAccessToken(
            $session->reveal(),
            new \DateTime('2020-03-30 12:15:00'),
            [],
            true
        )->shouldBeCalled()->willReturn('User token');

        $sheetUser1 = $this->prophesize(Sheet::class);
        $sheetUser1->getTitle()->shouldBeCalled()->willReturn('Paris Tech');
        $participant1 = $this->prophesize(Participant::class);
        $participant1->getSheet()->shouldBeCalled()->willReturn($sheetUser1->reveal());
        $userParticipant1 = $this->prophesize(User::class);
        $userParticipant1->getId()->shouldBeCalled()->willReturn(1234);
        $happeningParticipation1 = $this->prophesize(HappeningParticipation::class);
        $happening->getParticipations()->shouldBeCalled()->willReturn([$happeningParticipation1->reveal()]);
        $happeningParticipation1->getUser()->shouldBeCalled()->willReturn($userParticipant1->reveal());
        $this->getUserParticipantInfosHandler
            ->handle(new GetUserParticipantInfos($event->reveal(), $userParticipant1->reveal(), 'en'))
            ->shouldBeCalled()
            ->willReturn(new ParticipantView($participant1->reveal(), 'Amélie', 'POULAIN', 'Administrator', null));

        $this->assertEquals(
            new WebinarView(
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                'User token',
                'webinar-session-id',
                'api key',
                true,
                [
                    new WebinarSpeakerView(
                        1,
                        'Jeanne',
                        'Dupont',
                        'Développeuse',
                        'Fairness'
                    ),
                    new WebinarSpeakerView(
                        2,
                        'John',
                        'Doe',
                        'Ingénieur',
                        'Proximum'
                    ),
                ],
                [
                    new WebinarParticipantView(
                        1234,
                        'Amélie',
                        'POULAIN',
                        'Administrator',
                        'Paris Tech'
                    ),
                ],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                180,
                '/path/image.jpg'
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }
}
