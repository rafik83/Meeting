<?php


namespace Proximum\Vimeet\Tests\Application\Query\Networking;


use OpenTok\Session;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Exception\CallVisio\CallVisioNotAllowedException;
use Proximum\Vimeet\Application\Exception\Chat\ChatSessionNotFoundException;
use Proximum\Vimeet\Application\Query\Networking\CallVisioQuery;
use Proximum\Vimeet\Application\Query\Networking\CallVisioQueryHandler;
use Proximum\Vimeet\Application\View\Networking\CallVisioView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\VideoConferenceAdapter;

class CallVisioQueryHandlerTest extends TestCase
{
    /** @var \Prophecy\Prophecy\ObjectProphecy|ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Event */
    private $event;

    /** @var \Prophecy\Prophecy\ObjectProphecy|User */
    private $fromUser;

    /** @var \Prophecy\Prophecy\ObjectProphecy|User */
    private $toUser;

    /** @var \Prophecy\Prophecy\ObjectProphecy|NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var \Prophecy\Prophecy\ObjectProphecy|CallVisioPrivateChatAccessChecker */
    private $callVisioPrivateChatAccessChecker;

    /** @var \Prophecy\Prophecy\ObjectProphecy|VideoConferenceAdapter */
    private $videoConferenceAdapter;

    /** @var \Prophecy\Prophecy\ObjectProphecy|VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    /** @var \DateTimeImmutable|\Prophecy\Prophecy\ObjectProphecy */
    private $now;

    /** @var \Prophecy\Prophecy\ObjectProphecy|Sheet */
    private $sheet;

    /** @var \Prophecy\Prophecy\ObjectProphecy|ChatSession */
    private $chatSession;

    /** @var Session|\Prophecy\Prophecy\ObjectProphecy */
    private $session;

    /** @var \DateTimeImmutable|\Prophecy\Prophecy\ObjectProphecy */
    private $endTokenDateTime;

    /** @var \Prophecy\Prophecy\ObjectProphecy|VisioSettings */
    private $visioSetting;

    public function setUp(): void
   {
       $this->chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
       $this->chatSession = $this->prophesize(ChatSession::class);
       $this->event = $this->prophesize(Event::class);
       $this->fromUser = $this->prophesize(User::class);
       $this->toUser = $this->prophesize(User::class);
       $this->notificationSubscriber = $this->prophesize(NotificationSubscriberInterface::class);
       $this->callVisioPrivateChatAccessChecker = $this->prophesize(CallVisioPrivateChatAccessChecker::class);
       $this->videoConferenceAdapter = $this->prophesize(VideoConferenceAdapter::class);
       $this->visioSettingsRepository = $this->prophesize(VisioSettingsRepositoryInterface::class);
       $this->now = $this->prophesize(\DateTimeImmutable::class);
       $this->endTokenDateTime = new \DateTimeImmutable();
       $this->sheet = $this->prophesize(Sheet::class);
       $this->session = $this->prophesize(Session::class);
       $this->visioSetting = $this->prophesize(VisioSettings::class);
   }

    public function testNoChatSession(): void
    {
        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->chatSessionRepository->findOneByEventAndUsers($this->event->reveal(), $this->fromUser->reveal(), $this->toUser->reveal())->shouldBeCalled()->willReturn(null);
        $this->expectException(ChatSessionNotFoundException::class);

        $query = new CallVisioQuery($this->sheet->reveal(), $this->fromUser->reveal(), $this->toUser->reveal(),'FR');
        $handler = new CallVisioQueryHandler(
            $this->notificationSubscriber->reveal(),
            $this->chatSessionRepository->reveal(),
            $this->callVisioPrivateChatAccessChecker->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->visioSettingsRepository->reveal(),
            $this->now->reveal()
        );
        $handler->handle($query);
    }

    public function testNotAllowedToAccess(): void
    {
        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->chatSessionRepository->findOneByEventAndUsers($this->event->reveal(), $this->fromUser->reveal(), $this->toUser->reveal())->shouldBeCalled()->willReturn($this->chatSession->reveal());

        $this->callVisioPrivateChatAccessChecker->allowedToAccess($this->event->reveal(), $this->chatSession->reveal())->shouldBeCalled()->willReturn(false);
        $this->expectException(CallVisioNotAllowedException::class);

        $query = new CallVisioQuery($this->sheet->reveal(), $this->fromUser->reveal(), $this->toUser->reveal(),'FR');
        $handler = new CallVisioQueryHandler(
            $this->notificationSubscriber->reveal(),
            $this->chatSessionRepository->reveal(),
            $this->callVisioPrivateChatAccessChecker->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->visioSettingsRepository->reveal(),
            $this->now->reveal()
        );
        $handler->handle($query);
    }

    public function testCreateSession(): void
    {
        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->chatSessionRepository->findOneByEventAndUsers($this->event->reveal(), $this->fromUser->reveal(), $this->toUser->reveal())->shouldBeCalled()->willReturn($this->chatSession->reveal());

        $this->callVisioPrivateChatAccessChecker->allowedToAccess($this->event->reveal(), $this->chatSession->reveal())->shouldBeCalled()->willReturn(true);

        $this->chatSession->getVisioSessionId()->shouldBeCalled()->willReturn(null);

        $this->videoConferenceAdapter->createSession()->shouldBeCalled()->willReturn($this->session->reveal());
        $this->session->getSessionId()->shouldBeCalled()->willReturn(123456);
        $this->chatSession->setVisioSessionId(123456)->shouldBeCalled();
        $this->chatSessionRepository->update($this->chatSession->reveal())->shouldBeCalled();

        $this->now->add(new \DateInterval('PT1H'))->shouldBeCalled()->willReturn($this->endTokenDateTime);
        $this->videoConferenceAdapter->generateAccessToken($this->session->reveal(), $this->endTokenDateTime)->shouldBeCalled()->willReturn('token');

        $this->visioSettingsRepository->getByEvent($this->event->reveal())->shouldBeCalled()->willReturn($this->visioSetting->reveal());
        $this->notificationSubscriber->getCallVisioTopic($this->event->reveal())->shouldBeCalled()->willReturn('Networking');
        $this->notificationSubscriber->getUrl()->shouldBeCalled()->willReturn('http://www.google.fr');
        $this->notificationSubscriber->getUserSubscriberKey($this->sheet->reveal(), $this->fromUser->reveal())->shouldBeCalled()->willReturn('HRFGD45ghg56');
        $this->visioSetting->getHeader('FR')->shouldBeCalled()->willReturn('header');
        $this->visioSetting->getEndSound('FR')->shouldBeCalled()->willReturn('sound');
        $this->visioSetting->getEndImage('FR')->shouldBeCalled()->willReturn('endImage');
        $this->visioSetting->getEndMessage('FR')->shouldBeCalled()->willReturn('Message de fin');
        $this->videoConferenceAdapter->getApiKey()->shouldBeCalled()->willReturn('hiouh67jjkk');

        $query = new CallVisioQuery($this->sheet->reveal(), $this->fromUser->reveal(), $this->toUser->reveal(),'FR');
        $handler = new CallVisioQueryHandler(
            $this->notificationSubscriber->reveal(),
            $this->chatSessionRepository->reveal(),
            $this->callVisioPrivateChatAccessChecker->reveal(),
            $this->videoConferenceAdapter->reveal(),
            $this->visioSettingsRepository->reveal(),
            $this->now->reveal()
        );
        $result = $handler->handle($query);

        $expectedResult = new CallVisioView(
            'token',
            123456,
            'hiouh67jjkk',
            15*60,
            180,
            'header',
            'sound',
            'endImage',
            'Message de fin',
            'Networking',
            'http://www.google.fr',
            'HRFGD45ghg56'
        );

        $this->assertEquals($result, $expectedResult);
    }
}
