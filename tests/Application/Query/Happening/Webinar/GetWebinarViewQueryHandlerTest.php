<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar;

use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\Adapter\VideoConferenceAdapterInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQueryHandler;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfos;
use Proximum\Vimeet\Application\Query\User\Event\Participant\GetUserParticipantInfosHandler;
use Proximum\Vimeet\Application\Query\User\Event\Participant\ParticipantView;
use Proximum\Vimeet\Application\View\Happening\Notification\NotificationView;
use Proximum\Vimeet\Application\View\Happening\WebinarParticipantView;
use Proximum\Vimeet\Application\View\Happening\WebinarSpeakerView;
use Proximum\Vimeet\Application\View\Happening\Webinar\SpeakerWebinarView;
use Proximum\Vimeet\Application\View\Happening\Webinar\ViewerWebinarView;
use Proximum\Vimeet\Application\View\Happening\Webinar\WaitingMediaView;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordingAllowed;
use Proximum\Vimeet\Domain\MimeType\MimeType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Happening\HappeningBroadcast;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\HappeningBroadcastRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\Webinar\RecordArchiveRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeView;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class GetWebinarViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $getUserParticipantInfosHandler;

    /** @var ObjectProphecy */
    private $videoConferenceAdapter;

    /** @var ObjectProphecy */
    private $notificationSubscriber;

    /** @var ObjectProphecy */
   private $notificationSubscriptions;

    /** @var GetWebinarViewQueryHandler */
    private $getWebinarViewQueryHandler;

    /** @var \DateTime */
    private $dateTime;

    /** @var ObjectProphecy */
    private $recordArchiveRepository;

    /** @var ObjectProphecy */
    private $happeningBroadcastRepository;

    /** @var ObjectProphecy */
    private $isRecordingAllowed;

    /** @var ObjectProphecy */
    private $questionRepository;

    private const ALL_NOTIFICATIONS = [AbstractNotification::TYPE_CHAT, AbstractNotification::TYPE_QUESTIONS, AbstractNotification::TYPE_STREAM];

    protected function setUp(): void
    {
        $this->getUserParticipantInfosHandler = $this->prophesize(GetUserParticipantInfosHandler::class);
        $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapterInterface::class);
        $this->notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
        $this->notificationSubscriptions = $this->prophesize(NotificationSubscriptionsInterface::class);
        $this->recordArchiveRepository = $this->prophesize(RecordArchiveRepositoryInterface::class);
        $this->happeningBroadcastRepository = $this->prophesize(HappeningBroadcastRepositoryInterface::class);
        $this->isRecordingAllowed = $this->prophesize(IsRecordingAllowed::class);
        $this->dateTime = new \DateTime('2020-03-30 12:00:00');
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);

        $this->getWebinarViewQueryHandler = new GetWebinarViewQueryHandler(
            $this->getUserParticipantInfosHandler->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->notificationSubscriber->reveal(),
            $this->notificationSubscriptions->reveal(),
            $this->recordArchiveRepository->reveal(),
            $this->happeningBroadcastRepository->reveal(),
            $this->isRecordingAllowed->reveal(),
            $this->questionRepository->reveal(),
            $this->dateTime
        );
    }

    public function testHandle(): void
    {
        $user = $this->prophesize(User::class);
        $user->getFirstname()->shouldBeCalled()->willReturn('Michel');
        $user->getLastname()->shouldBeCalled()->willReturn('Dupont');
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
        $event->getAutoArchiveWebinar()->shouldBeCalled()->willReturn(false);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->shouldBeCalledTimes(2)->willReturn($event->reveal());
        $happening->getId()->shouldBeCalled()->willReturn(1);
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
        $happening->getLiveUrl()->shouldBeCalled()->willReturn('https://www.google.com/iframe?u=_firstname_%20_lastname_');
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(false);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);
        $this->isRecordingAllowed->isSatisfiedBy($happening->reveal())->shouldBeCalled()->willReturn(false);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);

        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);

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

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber
            ->getHappeningSubscriberKey(
                $happening->reveal(),
                $user->reveal(),
                self::ALL_NOTIFICATIONS
            )
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $this->assertEquals(
            new SpeakerWebinarView(
                137,
                1,
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                false,
                'User token',
                'webinar-session-id',
                'api key',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
                $speakerViews,
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                180,
                0,
                1585571400,
                '/path/image.jpg',
                'https://www.google.com/iframe?u=Michel%20Dupont',
                true,
                false,
                false,
                false,
                false,
                21,
                false,
                0,
                true
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }

    public function testHandleBeforeStart(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getAutoArchiveWebinar()->shouldBeCalled()->willReturn(false);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $date = new \DateTime('2020-03-30 11:50:00');
        $user = $this->prophesize(User::class);
        $user->getFirstname()->willReturn('Michel');
        $user->getLastname()->willReturn('Dupont');
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
        $happening->getId()->shouldBeCalled()->willReturn(1);
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
        $happening->getLiveUrl()->shouldBeCalled()->willReturn(null);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(false);
        $happening->getEvent()->shouldBeCalledTimes(2)->willReturn($event->reveal());
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);
        $this->isRecordingAllowed->isSatisfiedBy($happening->reveal())->shouldBeCalled()->willReturn(false);
        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);
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

        $getWebinarViewQueryHandler = new GetWebinarViewQueryHandler(
            $this->getUserParticipantInfosHandler->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->notificationSubscriber->reveal(),
            $this->notificationSubscriptions->reveal(),
            $this->recordArchiveRepository->reveal(),
            $this->happeningBroadcastRepository->reveal(),
            $this->isRecordingAllowed->reveal(),
            $this->questionRepository->reveal(),
            $date
        );

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber
            ->getHappeningSubscriberKey(
                $happening->reveal(),
                $user->reveal(),
                self::ALL_NOTIFICATIONS
            )
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $this->assertEquals(
            new SpeakerWebinarView(
                137,
                1,
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                false,
                'User token',
                'webinar-session-id',
                'api key',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
                $speakerViews,
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $date,
                1500,
                300,
                300,
                1585571400,
                '/path/image.jpg',
                null,
                true,
                false,
                false,
                false,
                false,
                21,
                false,
                0,
                true
            ),
            $getWebinarViewQueryHandler->handle(
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
        $event->getAutoArchiveWebinar()->shouldBeCalled()->willReturn(false);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->shouldBeCalled()->willReturn(1);
        $happening->getTitle('en')->shouldBeCalled()->willReturn(
            'Webinar: how to work remotely during the Covid-19 crisis'
        );
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(true);
        $happening->getSpeakers()->shouldBeCalled()->willReturn($speakers);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $happening->getLiveUrl()->shouldBeCalled()->willReturn(null);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(false);
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(false);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $this->isRecordingAllowed->isSatisfiedBy($happening->reveal())->shouldBeCalled()->willReturn(true);
        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);

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

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber
            ->getHappeningSubscriberKey(
                $happening->reveal(),
                $user->reveal(),
                self::ALL_NOTIFICATIONS
            )
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $this->recordArchiveRepository->hasStartedRecordArchiveForHappening($happening->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->assertEquals(
            new SpeakerWebinarView(
                137,
                1,
                111,
                'Webinar: how to work remotely during the Covid-19 crisis',
                false,
                'User token',
                'webinar-session-id',
                'api key',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
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
                0,
                1585571400,
                '/path/image.jpg',
                null,
                true,
                false,
                true,
                true,
                false,
                21,
                false,
                0,
                false
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }

    public function testHandleEndedVideoWebinar(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(111);

        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $happening->getId()->shouldBeCalled()->willReturn(1);
        $happening->getTitle('en')->shouldBeCalled()->willReturn(
            'Video Webinar: how to work remotely during the Covid-19 crisis'
        );
        $happening->hasWebinarSessionId()->shouldNotBeCalled();
        $happening->getWebinarSessionId()->shouldNotBeCalled();
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(false);
        $happening->getSpeakers()->shouldNotBeCalled();
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:00:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:45:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getWebinarWaitingMediaFile('en')->shouldBeCalled()->willReturn(null);
        $happening->getWebinarWaitingMediaType('en')->shouldBeCalled()->willReturn(null);
        $happening->getLiveUrl()->shouldBeCalled()->willReturn('https://www.utube.com/embed/whatever');
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(true);
        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(false);

        $this->videoConferenceAdapter->getSession(Argument::any())->shouldNotBeCalled();
        $this->videoConferenceAdapter->getApiKey()->shouldNotBeCalled();
        $this->videoConferenceAdapter->generateAccessToken(Argument::any())->shouldNotBeCalled();

        $happening->getParticipations()->shouldNotBeCalled();
        $this->getUserParticipantInfosHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber->getHappeningSubscriberKey($happening->reveal(), $user->reveal(), self::ALL_NOTIFICATIONS)
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $this->assertEquals(
            new ViewerWebinarView(
                137,
                1,
                111,
                'Video Webinar: how to work remotely during the Covid-19 crisis',
                true,
                '',
                '',
                '',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
                [],
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:00:00'), new \DateTime('2020-03-30 11:45:00')),
                $this->dateTime,
                0,
                '/path/image.jpg',
                new WaitingMediaView(null, null),
                'https://www.utube.com/embed/whatever',
                true,
                true,
                21,
                false,
                null
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }

    public function testHandleVideoWebinarBroadcastSpeaker(): void
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
        $event->getAutoArchiveWebinar()->shouldBeCalled()->willReturn(false);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->shouldBeCalledTimes(2)->willReturn($event->reveal());
        $happeningId = 1;
        $happening->getId()->shouldBeCalled()->willReturn($happeningId);
        $happening->getTitle('en')->shouldBeCalled()->willReturn('Lorem ipsum dolor sit');
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(true);
        $happening->getSpeakers()->shouldBeCalled()->willReturn($speakers);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getLiveUrl()->willReturn(null);
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(false);
        $happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);
        $this->isRecordingAllowed->isSatisfiedBy($happening->reveal())->shouldBeCalled()->willReturn(false);
        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(true);

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

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber
            ->getHappeningSubscriberKey(
                $happening->reveal(),
                $user->reveal(),
                self::ALL_NOTIFICATIONS
            )
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $this->notificationSubscriptions->getStreamSubscriptionsCount($happeningId)->shouldBeCalled()->willReturn(42);

        $this->assertEquals(
            new SpeakerWebinarView(
                137,
                $happeningId,
                111,
                'Lorem ipsum dolor sit',
                false,
                'User token',
                'webinar-session-id',
                'api key',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
                $speakerViews,
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                180,
                0,
                1585571400,
                '/path/image.jpg',
                null,
                true,
                false,
                false,
                false,
                false,
                21,
                true,
                42,
                true
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }

    public function testHandleVideoWebinarBroadcastViewer(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(111);

        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(137);
        $happening = $this->prophesize(Happening::class);
        $happening->getEvent()->willReturn($event->reveal());
        $happeningId = 1558;
        $happening->getId()->shouldBeCalled()->willReturn($happeningId);
        $happening->getTitle('en')->shouldBeCalled()->willReturn('Lorem ipsum dolor sit');
        $happening->hasWebinarSessionId()->shouldBeCalled()->willReturn(true);
        $happening->getWebinarSessionId()->shouldBeCalled()->willReturn('webinar-session-id');
        $happening->isInteractiveWebinar()->shouldBeCalled()->willReturn(false);
        $happening->hasSpeaker($user->reveal())->shouldBeCalled()->willReturn(false);
        $happening->getSpeakers()->shouldBeCalled()->willReturn([]);
        $happening->getBegin()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 11:55:00'));
        $happening->getEnd()->shouldBeCalled()->willReturn(new \DateTime('2020-03-30 12:15:00'));
        $happening->getWebinarHeaderImage('en')->shouldBeCalled()->willReturn('/path/image.jpg');
        $happening->getWebinarWaitingMediaFile('en')->shouldBeCalled()->willReturn('/path/to/video.mp4');
        $happening->getWebinarWaitingMediaType('en')->shouldBeCalled()->willReturn(MimeType::FORMAT_VIDEO);
        $happening->getLiveUrl()->willReturn(null);
        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->shouldBeCalled()->willReturn(21);
        $happening->isSidebarAllowed()->shouldBeCalled()->willReturn(true);
        $happening->isVideoWebinarAndHasLiveUrl()->shouldBeCalled()->willReturn(false);
        $happening->allowWebinarOnHLS()->shouldBeCalled()->willReturn(true);

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
            false
        )->shouldBeCalled()->willReturn('User token');

        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://notification-hub.dev/entrypoint');
        $this->notificationSubscriber
            ->getHappeningSubscriberKey($happening->reveal(), $user->reveal(), self::ALL_NOTIFICATIONS)
            ->shouldBeCalled()
            ->willReturn('xxxxyyy');

        $happeningBroadcast = new HappeningBroadcast(
            $happening->reveal(),
            '12345',
            false,
            new \DateTimeImmutable('2020-10-27 10:30:00'),
            new \DateTimeImmutable('2020-10-27 12:30:00'),
            'http://some-video-provider.com/stream.hls'
        );
        $this->happeningBroadcastRepository->getByHappening($happening->reveal())->shouldBeCalled()->willReturn($happeningBroadcast);

        $this->assertEquals(
            new ViewerWebinarView(
                137,
                $happeningId,
                111,
                'Lorem ipsum dolor sit',
                false,
                'User token',
                'webinar-session-id',
                'api key',
                new NotificationView('http://notification-hub.dev/entrypoint', 'xxxxyyy'),
                [],
                [],
                new TimeRangeView(new \DateTime('2020-03-30 11:55:00'), new \DateTime('2020-03-30 12:15:00')),
                $this->dateTime,
                900,
                '/path/image.jpg',
                new WaitingMediaView('/path/to/video.mp4', MimeType::FORMAT_VIDEO),
                null,
                true,
                false,
                21,
                true,
                'http://some-video-provider.com/stream.hls'
            ),
            $this->getWebinarViewQueryHandler->handle(
                new GetWebinarViewQuery($happening->reveal(), $user->reveal(), 'en')
            )
        );
    }
}
