<?php

namespace Proximum\Vimeet\Tests\Application\Query\Networking;

use DateTime;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Application\Query\Networking\NetworkingQuery;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Query\Networking\NetworkingQueryHandler;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\ChatSessionView;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;

class NetworkingQueryHandlerTest extends TestCase
{
    private $user, $participant, $event, $sheet, $notificationSubescriber, $notificationSubscribtions, $chatSessionRepository, $chatMessageRepository;

    public function setUp(): void
    {

        $this->user = $this->prophesize(User::class);
        $this->participant = $this->prophesize(Participant::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->notificationSubescriber = $this->prophesize(NotificationSubscriberInterface::class);
        $this->notificationSubscribtions  = $this->prophesize(NotificationSubscriptionsInterface::class);
        $this->chatSessionRepository = $this->prophesize(ChatSessionRepositoryInterface::class);
        $this->chatMessageRepository = $this->prophesize(ChatMessageRepositoryInterface::class);
    }

    public function testShouldReturnAViewWithoutSessions(): void
    {

        $eventId = 555;
        $topic = "faketopic";
        $providerUrl = "http://tourte.com";
        $fakeSubscriberKey = "a fake subscriber key";
        $now = new DateTime();
        $messageCount = 333;

        $fakeSubscriptions = [];
        $sessionsByEventAndUser = [];
        $privateChatSessions = [];

        $userId = 1615;
        $this->user->getId()->shouldBeCalled()->willReturn($userId);

        $this->participant->getNetworkingChatViewedAt()->shouldBeCalled()->willReturn($now);

        $this->event->getId()->shouldBeCalled()->willReturn($eventId);

        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $this->notificationSubescriber->getNetworkingTopic($eventId)->shouldBecalled()->willReturn($topic);
        $this->notificationSubescriber->getUrl()->shouldBecalled()->willReturn($providerUrl);
        $this->notificationSubescriber->getNetworkingSubscriberKey($this->sheet->reveal(), $this->user->reveal(), [AbstractNotification::TYPE_CHAT])->shouldBecalled()->willReturn($fakeSubscriberKey);


        $this->notificationSubscribtions->getSubscriptions($eventId, $userId)->shouldBeCalled()->willReturn($fakeSubscriptions);

        $this->chatSessionRepository->findSessionsByEventAndUser($this->event->reveal(), $this->user)->shouldBeCalled()->willReturn($sessionsByEventAndUser);
        $this->chatMessageRepository->getMessagesCountByLinkableObject($this->event->reveal(), $now)->shouldBeCalled()->willReturn($messageCount);

        $query = new NetworkingQuery($this->sheet->reveal(), $this->user->reveal());

        $handler = new NetworkingQueryHandler($this->notificationSubescriber->reveal(), $this->notificationSubscribtions->reveal(), $this->chatMessageRepository->reveal(), $this->chatSessionRepository->reveal());

        $result =  $handler->handle($query);

        $expectedResult = new NetworkingView($providerUrl, $fakeSubscriberKey, $topic, $fakeSubscriptions, $userId, $messageCount, $privateChatSessions);

        $this->assertEquals($result, $expectedResult);
    }

    public function testShouldReturnAViewWithSessionsForAUser(): void
    {

        $otherUser = $this->prophesize(User::class);

        $eventId = 555;
        $topic = "faketopic";
        $providerUrl = "http://tourte.com";
        $fakeSubscriberKey = "a fake subscriber key";
        $now = new DateTime();
        $messageCount = 333;


        $userId = 1615;
        $fakeSubscriptions = [];

        $newMessageCount = 666;
        $totalMessageCount = 55;

        $sessionsByEventAndUser = [[
            "otherUser" =>  $otherUser->reveal(),
            "latestMessageDate" => $now,
            "messagesCount" =>  $totalMessageCount,
            "unreadMessages" => [
                $userId =>   $newMessageCount
            ]
        ]];

        $privateChatSessions = [new ChatSessionView($otherUser->reveal(), $now, $totalMessageCount, $newMessageCount)];


        $this->user->getId()->shouldBeCalled()->willReturn($userId);

        $this->participant->getNetworkingChatViewedAt()->shouldBeCalled()->willReturn($now);

        $this->event->getId()->shouldBeCalled()->willReturn($eventId);


        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $this->notificationSubescriber->getNetworkingTopic($eventId)->shouldBecalled()->willReturn($topic);
        $this->notificationSubescriber->getUrl()->shouldBecalled()->willReturn($providerUrl);
        $this->notificationSubescriber->getNetworkingSubscriberKey($this->sheet->reveal(), $this->user->reveal(), [AbstractNotification::TYPE_CHAT])->shouldBecalled()->willReturn($fakeSubscriberKey);


        $this->notificationSubscribtions->getSubscriptions($eventId, $userId)->shouldBeCalled()->willReturn($fakeSubscriptions);

        $this->chatSessionRepository->findSessionsByEventAndUser($this->event->reveal(), $this->user->reveal())->shouldBeCalled()->willReturn($sessionsByEventAndUser);
        $this->chatMessageRepository->getMessagesCountByLinkableObject($this->event->reveal(), $now)->shouldBeCalled()->willReturn($messageCount);

        $query = new NetworkingQuery($this->sheet->reveal(), $this->user->reveal());

        $handler = new NetworkingQueryHandler($this->notificationSubescriber->reveal(), $this->notificationSubscribtions->reveal(), $this->chatMessageRepository->reveal(), $this->chatSessionRepository->reveal());

        $result =  $handler->handle($query);

        $expectedResult = new NetworkingView($providerUrl, $fakeSubscriberKey, $topic, $fakeSubscriptions, $userId, $messageCount, $privateChatSessions);

        $this->assertEquals($result, $expectedResult);
    }

    public function testShouldReturnZeroPrivateChatSessionsIfNoneAreForTheUser(): void
    {

        $otherUser = $this->prophesize(User::class);

        $eventId = 555;
        $topic = "faketopic";
        $providerUrl = "http://tourte.com";
        $fakeSubscriberKey = "a fake subscriber key";
        $now = new DateTime();
        $messageCount = 333;


        $otherUserId = 333;
        $userId = 1615;
        $fakeSubscriptions = [];

        $newMessageCount = 666;
        $totalMessageCount = 55;

        // This Sesssion has no messages for user with id 1615 (our User)

        $sessionsByEventAndUser = [[
            "otherUser" =>  $otherUser->reveal(),
            "latestMessageDate" => $now,
            "messagesCount" =>  $totalMessageCount,
            "unreadMessages" => [
                $otherUserId =>   $newMessageCount
            ]
        ]];




        $privateChatSessions = [new ChatSessionView($otherUser->reveal(), $now, $totalMessageCount, 0)];


        $this->user->getId()->shouldBeCalled()->willReturn($userId);

        $this->participant->getNetworkingChatViewedAt()->shouldBeCalled()->willReturn($now);

        $this->event->getId()->shouldBeCalled()->willReturn($eventId);


        $this->sheet->getEvent()->shouldBeCalled()->willReturn($this->event->reveal());
        $this->sheet->getUserParticipant($this->user->reveal())->shouldBeCalled()->willReturn($this->participant->reveal());

        $this->notificationSubescriber->getNetworkingTopic($eventId)->shouldBecalled()->willReturn($topic);
        $this->notificationSubescriber->getUrl()->shouldBecalled()->willReturn($providerUrl);
        $this->notificationSubescriber->getNetworkingSubscriberKey($this->sheet->reveal(), $this->user->reveal(), [AbstractNotification::TYPE_CHAT])->shouldBecalled()->willReturn($fakeSubscriberKey);

        $this->notificationSubscribtions->getSubscriptions($eventId, $userId)->shouldBeCalled()->willReturn($fakeSubscriptions);

        $this->chatSessionRepository->findSessionsByEventAndUser($this->event->reveal(), $this->user->reveal())->shouldBeCalled()->willReturn($sessionsByEventAndUser);
        $this->chatMessageRepository->getMessagesCountByLinkableObject($this->event->reveal(), $now)->shouldBeCalled()->willReturn($messageCount);

        $query = new NetworkingQuery($this->sheet->reveal(), $this->user->reveal());

        $handler = new NetworkingQueryHandler($this->notificationSubescriber->reveal(), $this->notificationSubscribtions->reveal(), $this->chatMessageRepository->reveal(), $this->chatSessionRepository->reveal());

        $result =  $handler->handle($query);

        $expectedResult = new NetworkingView($providerUrl, $fakeSubscriberKey, $topic, $fakeSubscriptions, $userId, $messageCount, $privateChatSessions);

        $this->assertEquals($result, $expectedResult);
    }
}
