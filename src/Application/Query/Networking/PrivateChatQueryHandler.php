<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Exception\Chat\PrivateChatInvalidToUser;
use Proximum\Vimeet\Application\View\Networking\PrivateChatView;
use Proximum\Vimeet\Domain\KeyDates\Checker\AskCallVisioPrivateChatAccessChecker;
use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class PrivateChatQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    /** @var AskCallVisioPrivateChatAccessChecker */
    private $askCallVisioPrivateChatAccessChecker;

    /** @var IsUserInCallVisio */
    private $isUserInCallVisio;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        ChatSessionRepositoryInterface $chatSessionRepository,
        AskCallVisioPrivateChatAccessChecker $callVisioPrivateChatAccessChecker,
        IsUserInCallVisio $isUserInCallVisio
    )
    {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->askCallVisioPrivateChatAccessChecker = $callVisioPrivateChatAccessChecker;
        $this->isUserInCallVisio = $isUserInCallVisio;
    }

    public function handle(PrivateChatQuery $privateChatQuery): PrivateChatView
    {
        if ($privateChatQuery->fromUser->getId() === $privateChatQuery->toUser->getId()) {
            throw new PrivateChatInvalidToUser('User cannot open a chat session with himself');
        }

        $event = $privateChatQuery->sheet->getEvent();

        $chatSession = $this->chatSessionRepository->findOneByEventAndUsers(
            $event,
            $privateChatQuery->fromUser,
            $privateChatQuery->toUser
        );

        if (null === $chatSession) {
            $chatSession = new ChatSession(
                $event,
                $privateChatQuery->fromUser,
                $privateChatQuery->toUser
            );

            $this->chatSessionRepository->add($chatSession);
        }

        $topic = $this->notificationSubscriber->getUserTopic($event->getId(), $privateChatQuery->fromUser->getId());

        $hasVisioButton = $this->askCallVisioPrivateChatAccessChecker->allowedToAccess($event, $chatSession, $privateChatQuery->toUser);

        $isToUserBusy = $this->isUserInCallVisio->isSatisfiedBy($event, $privateChatQuery->toUser);

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
            $hasVisioButton,
            $isToUserBusy
        );
    }
}
