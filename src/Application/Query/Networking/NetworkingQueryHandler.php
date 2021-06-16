<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
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

    private RouterInterface $routerAdapter;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NotificationSubscriptionsInterface $notificationSubscriptions,
        ChatMessageRepositoryInterface $chatMessageRepository,
        ChatSessionRepositoryInterface $chatSessionRepository,
        RouterInterface $routerAdapter
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->chatMessageRepository = $chatMessageRepository;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->routerAdapter = $routerAdapter;
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
                $avatar = $row['otherUser']->getAvatar();
                if ($avatar === null) {
                    $avatarUrl = $this->routerAdapter->generate(
                        'event_chat_avatar',
                        ['name' => $row['otherUser']->getAccount()->getCompleteName()]
                    );
                } else {
                    $avatarUrl = $this->routerAdapter->generate(
                        'liip_imagine_filter',
                        ['path' => $avatar, 'filter' => 'user_icon']
                    );
                }

                return new ChatSessionView(
                    $row['otherUser'],
                    $avatarUrl,
                    $row['latestMessageDate'],
                    $row['messagesCount'],
                    $row['unreadMessages'][$networkingQuery->user->getId()] ?? 0
                );
            },
            $this->chatSessionRepository->findSessionsByEventAndUser($networkingQuery->sheet->getEvent(), $networkingQuery->user)
        );

        $privateChatNewMessages = 0;
        foreach($privateChatSessions as $chatSessionView) {
            $privateChatNewMessages += $chatSessionView->newMessagesCount;
        }

        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->sheet, $networkingQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($networkingQuery->sheet->getEvent()->getId(), $networkingQuery->user->getId()),
            $networkingQuery->user->getId(),
            $networkingChatNewMessagesCount,
            $privateChatSessions,
            $privateChatNewMessages
        );
    }
}
