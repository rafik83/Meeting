<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\ChatSessionView;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class NetworkingQueryHandler
{

    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NotificationSubscriptionsInterface $notificationSubscriptions,
        ChatSessionRepositoryInterface $chatSessionRepository
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function handle(NetworkingQuery $networkingQuery): NetworkingView
    {

        $topic = $this->notificationSubscriber->getNetworkingTopic($networkingQuery->sheet->getEvent()->getId());

        $privateChatSessions = array_map(
            function ($row) {
                return new ChatSessionView(
                    $row['otherUser'],
                    $row['latestMessageDate'],
                    $row['messagesCount']
                );
            },
            $this->chatSessionRepository->findByEventAndUser($networkingQuery->sheet->getEvent(), $networkingQuery->user)
        );

        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->sheet, $networkingQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($networkingQuery->sheet->getEvent()->getId(), $networkingQuery->user->getId()),
            $networkingQuery->user->getId(),
            $privateChatSessions
        );
    }
}
