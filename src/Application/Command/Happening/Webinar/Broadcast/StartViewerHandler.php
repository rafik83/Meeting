<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Broadcast;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Adapter\NotificationSubscriptionsInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class StartViewerHandler
{
    /** @var NotificationSubscriptionsInterface */
    private $notificationSubscriptions;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        NotificationSubscriptionsInterface $notificationSubscriptions,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->notificationSubscriptions = $notificationSubscriptions;
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(StartViewer $command): void
    {
        $happening = $command->happening;

        $connectedUsersCount = $this->notificationSubscriptions->getStreamSubscriptionsCount($happening->getId());

        $this->notificationPublisher->publishHappeningNotification($happening, AbstractNotification::TYPE_STREAM, [
            'action' => 'viewer_connected',
            'connectedUsersCount' => $connectedUsersCount,
        ]);
    }
}
