<?php


namespace Proximum\Vimeet\Application\Query\Networking;


use Proximum\Vimeet\Application\Adapter\NotificationSubscriberInterface;
use Proximum\Vimeet\Application\View\Networking\NetworkingView;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class NetworkingQueryHandler
{

    /** @var NotificationSubscriberInterface */
    private $notificationSubscriber;

    public function __construct(
        NotificationSubscriberInterface $notificationSubscriber
    ){

        $this->notificationSubscriber = $notificationSubscriber;
    }

    public function handle(NetworkingQuery $networkingQuery): NetworkingView
    {
        return new NetworkingView(
            $this->notificationSubscriber->getUrl(),
            $this->notificationSubscriber->getNetworkingSubscriberKey($networkingQuery->event, $networkingQuery->user->getId(), [AbstractNotification::TYPE_QUESTIONS]),
            $this->notificationSubscriber->getNotificationTopic($networkingQuery->event->getId(), 'networking', $networkingQuery->event->getId())
        );
    }
}
