<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class NetworkingQueryHandler
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

    public function handle(NetworkingQuery $networkingQuery): NetworkingView
    {

        $topic = $this->notificationSubscriber->getNotificationTopic($networkingQuery->event->getId());

        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->event, $networkingQuery->user, [AbstractNotification::TYPE_CHAT]),
            $topic,
            $this->notificationSubscriptions->getSubscriptions($topic)
        );
    }
}
