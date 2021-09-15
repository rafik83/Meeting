<?php

namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;

class IsUserInCallVisio
{
    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    public function __construct(
        NotificationSubscriptionsInterface $notificationSubscriptions,
        NotificationSubscriberInterface $notificationSubscriber
    ) {
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->notificationSubscriber = $notificationSubscriber;
    }

    public function isSatisfiedBy(Event $event, User $user): bool
    {
        $callVisioTopic = $this->notificationSubscriber->getCallVisioTopic($event);
        $subscribedUsers = $this->notificationSubscriptions->getSubscriptions(
            $event->getId(),
            null,
            $callVisioTopic
        );

        foreach ($subscribedUsers as $subscribedUser) {
            if ($subscribedUser['userId'] === $user->getId()) {
                return true;
            }
        }

        return false;
    }
}
