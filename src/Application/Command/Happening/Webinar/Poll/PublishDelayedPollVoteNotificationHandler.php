<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class PublishDelayedPollVoteNotificationHandler
{
    private NotificationPublisherInterface $notificationPublisher;
    private PollRepositoryInterface $pollRepository;

    public function __construct(
        NotificationPublisherInterface $notificationPublisher,
        PollRepositoryInterface $pollRepository
    ) {
        $this->notificationPublisher = $notificationPublisher;
        $this->pollRepository = $pollRepository;
    }

    public function handle(PublishDelayedPollVoteNotificationMessage $message)
    {
        $poll = $this->pollRepository->findById($message->getPollId());
        if ($poll === null) {
            throw new PollNotFoundException(sprintf('No poll with id %d', $message->getPollId()));
        }

        $this->notificationPublisher->publishedPollVoteNotification($poll);
    }
}
