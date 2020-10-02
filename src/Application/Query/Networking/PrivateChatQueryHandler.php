<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\PrivateChatView;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class PrivateChatQueryHandler
{
    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber,
        NotificationSubscriptionsInterface $notificationSubscriptions
    )
    {
        $this->notificationSubscriber = $notificationSubscriber;
        $this->notificationSubscriptions = $notificationSubscriptions;
    }

    public function handle(PrivateChatQuery $privateChatQuery): PrivateChatView
    {

        $topic = $this->notificationSubscriber->getNotificationTopic($privateChatQuery->event->getId());

        return new PrivateChatView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($privateChatQuery->event, $privateChatQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($topic)
        );
    }
}
