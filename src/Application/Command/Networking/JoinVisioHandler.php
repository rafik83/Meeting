<?php


namespace Proximum\Vimeet\Application\Command\Networking;


use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Infrastructure\Repository\ChatSessionRepository;


class JoinVisioHandler
{
    /** @var NotificationPublisherInterface */
    private $notificationPublisher;

    /** @var ChatSessionRepository */
    private $chatSessionRepository;

    /** @var \DateTimeImmutable */
    private $now;

    public function __construct(
        NotificationPublisherInterface $notificationPublisher,
        ChatSessionRepository $chatSessionRepository,
        \DateTimeImmutable $now
    ) {
        $this->notificationPublisher = $notificationPublisher;
        $this->chatSessionRepository = $chatSessionRepository;
        $this->now = $now;
    }

    public function handle(JoinVisio $command): void
    {
        $chatSession = $this->chatSessionRepository->findOneByEventAndUsers($command->sheet->getEvent(), $command->fromUser, $command->toUser);
        $chatSession->setVisioStartedAt($this->now);
        $this->chatSessionRepository->update();
        $this->notificationPublisher->publishRequestVisioNotification(
            $command->sheet,
            $command->fromUser,
            $command->toUser->getId(),
            'join_visio'
        );
    }
}

