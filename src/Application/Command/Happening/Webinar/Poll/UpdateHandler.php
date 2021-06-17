<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class UpdateHandler
{
    private PollRepositoryInterface $pollRepository;
    private NotificationPublisherInterface $notificationPublisher;

    public function __construct(
        PollRepositoryInterface $pollRepository,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->pollRepository = $pollRepository;
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(Update $command)
    {
        $poll = $this->pollRepository->findById($command->pollId);

        if ($poll === null) {
            throw new PollNotFoundException(sprintf('No poll with id %d', $command->pollId));
        }

        if ($poll->getHappening()->getId() !== $command->happeningId) {
            throw new PollHappeningMismatchException(sprintf('Poll %d is not in this happening', $command->pollId));
        }

        $poll->update($command->title, $command->choices, $command->multipleChoice);

        if ($command->publish) {
            $poll->setPublished();
        }

        $this->pollRepository->update($poll);

        if ($command->publish) {
            $this->notificationPublisher->publishNewPublishedPollNotification($poll);
        }
    }
}
