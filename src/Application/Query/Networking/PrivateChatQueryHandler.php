<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Exception\Chat\PrivateChatInvalidToUser;
use Proximum\Vimeet\Application\View\Networking\PrivateChatView;
use Proximum\Vimeet\Domain\KeyDates\Checker\CallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class PrivateChatQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var CallVisioPrivateChatAccessChecker */
    private $callVisioPrivateChatAccessChecker;

    /** @var bool */
    public $isVisio;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        ChatSessionRepositoryInterface $chatSessionRepository,
        CallVisioPrivateChatAccessChecker $callVisioPrivateChatAccessChecker
    ) {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->callVisioPrivateChatAccessChecker = $callVisioPrivateChatAccessChecker;
    }

    public function handle(PrivateChatQuery $privateChatQuery): PrivateChatView
    {
        if ($privateChatQuery->fromUser->getId() === $privateChatQuery->toUser->getId()) {
            throw new PrivateChatInvalidToUser('User cannot open a chat session with himself');
        }

        $chatSession = $this->chatSessionRepository->findOneByEventAndUsers(
            $privateChatQuery->sheet->getEvent(),
            $privateChatQuery->fromUser,
            $privateChatQuery->toUser
        );

        if (null === $chatSession) {
            $chatSession = new ChatSession(
                $privateChatQuery->sheet->getEvent(),
                $privateChatQuery->fromUser,
                $privateChatQuery->toUser
            );

            $this->chatSessionRepository->add($chatSession);
        }

        $topic = $this->notificationSubscriber->getUserTopic($privateChatQuery->sheet->getEvent()->getId(), $privateChatQuery->fromUser->getId());

        $isVisio = $this->callVisioPrivateChatAccessChecker->allowedToAccess($privateChatQuery->sheet->getEvent());

        return new PrivateChatView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey(
                $privateChatQuery->sheet,
                $privateChatQuery->toUser,
                [AbstractNotification::TYPE_CHAT]
            ),
            $topic,
            $privateChatQuery->toUser->getFirstName(),
            $privateChatQuery->toUser->getLastName(),
            $privateChatQuery->toUser->getAccount()->getCompany(),
            $privateChatQuery->toUser->getPosition(),
            $privateChatQuery->toUser->getId(),
            $chatSession->getId(),
            $isVisio
        );
    }
}
