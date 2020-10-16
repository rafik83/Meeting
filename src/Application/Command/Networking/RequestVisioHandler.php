<?php

namespace Proximum\Vimeet\Application\Command\Networking;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Repository\ChatMessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;

class RequestVisioHandler
{
    /** @var ChatMessageRepositoryInterface */
    private $chatSessionRepository;

    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    public function __construct(
        ChatSessionRepositoryInterface $chatSessionRepository,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->chatSessionRepository = $chatSessionRepository;
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(RequestVisio $command)
    {
        $this->notificationPublisher->publishRequestVisioNotification(
            $command->sheet,
            $command->fromUser,
            $command->toUser->getId()
        );
    }
}
