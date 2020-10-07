<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\ChatSessionView;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class NetworkingQueryHandler
{

    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NotificationSubscriptionsInterface $notificationSubscriptions,
        ChatSessionRepositoryInterface $chatSessionRepository,
        UserRepositoryInterface $userRepository
    )
    {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(NetworkingQuery $networkingQuery): NetworkingView
    {

        $topic = $this->notificationSubscriber->getNotificationTopic($networkingQuery->event->getId());

        $privateChatSessions = array_map(
            function ($row) {
                return new ChatSessionView(
                    $this->userRepository->findOneById($row['otherUserId']),
                    $row['latestMessageDate'],
                    $row['messagesCount']
                );
            },
            $this->chatSessionRepository->findByEventAndUser($networkingQuery->event, $networkingQuery->user)
        );

        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->event, $networkingQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($topic),
            $privateChatSessions
        );
    }
}
