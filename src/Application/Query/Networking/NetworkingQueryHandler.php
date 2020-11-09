<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\ChatSessionView;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

// todo: add unit test
class NetworkingQueryHandler
{

    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    /** @var ChatMessageRepositoryInterface */
    private $chatMessageRepository;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NotificationSubscriptionsInterface $notificationSubscriptions,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatSessionRepositoryInterface $chatSessionRepository
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function handle(NetworkingQuery $networkingQuery): NetworkingView
    {

        $topic = $this->notificationSubscriber->getNetworkingTopic($networkingQuery->sheet->getEvent()->getId());

        $networkingChatNewMessagesCount = $this->chatMessageRepository->getMessagesCountByLinkableObject(

            $networkingQuery->sheet->getEvent(),
            $networkingQuery->sheet->getUserParticipant($networkingQuery->user)->getNetworkingChatViewedAt()
        );

        $privateChatSessions = array_map(
            function ($row) use ($networkingQuery) {
                return new ChatSessionView(
                    $row['otherUser'],
                    $row['latestMessageDate'],
                    $row['messagesCount'],
                    $row['unreadMessages'][$networkingQuery->user->getId()] ?? 0
                );
            },
            $this->chatSessionRepository->findSessionsByEventAndUser($networkingQuery->sheet->getEvent(), $networkingQuery->user)
        );

        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->sheet, $networkingQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($networkingQuery->sheet->getEvent()->getId(), $networkingQuery->user->getId()),
            $networkingQuery->user->getId(),
            $networkingChatNewMessagesCount,
            $privateChatSessions
        );
    }
}
