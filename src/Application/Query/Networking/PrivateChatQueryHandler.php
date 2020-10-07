<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Exception\Chat\PrivateChatInvalidToUser;
use Proximum\Vimeet\Application\View\Networking\PrivateChatView;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class PrivateChatQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        ChatSessionRepositoryInterface $chatSessionRepository
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function handle(PrivateChatQuery $privateChatQuery): PrivateChatView
    {
        if ($privateChatQuery->fromUser->getId() !== $privateChatQuery->toUser->getId()) {
            throw new PrivateChatInvalidToUser('User cannot open a chat session with himself');
        }

        $chatSession = $this->chatSessionRepository->findOneByEventAndUsers(
            $privateChatQuery->event,
            $privateChatQuery->fromUser,
            $privateChatQuery->toUser
        );

        if (null === $chatSession) {
            $chatSession = new ChatSession(
                $privateChatQuery->event,
                $privateChatQuery->fromUser,
                $privateChatQuery->toUser
            );

            $this->chatSessionRepository->add($chatSession);
        }

        $topic = $this->notificationSubscriber->getChatSessionTopic($chatSession);

        return new PrivateChatView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey(
                $privateChatQuery->event,
                $privateChatQuery->toUser,
                [AbstractNotification::TYPE_CHAT]
            ),
            $topic,
            $privateChatQuery->toUser->getFirstName(),
            $privateChatQuery->toUser->getLastName(),
            $privateChatQuery->toUser->getAccount()->getCompany(),
            $privateChatQuery->toUser->getPosition(),
            $privateChatQuery->toUser->getId(),
            $chatSession->getId()
        );
    }
}
