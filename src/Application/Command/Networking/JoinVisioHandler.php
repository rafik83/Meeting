<?php


namespace Proximum\Vimeet\Application\Command\Networking;


use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;


class JoinVisioHandler
{
    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(JoinVisio $command)
    {
        $this->notificationPublisher->publishRequestVisioNotification(
            $command->sheet,
            $command->fromUser,
            $command->toUser->getId(),
            'join_visio'
        );
    }
}

