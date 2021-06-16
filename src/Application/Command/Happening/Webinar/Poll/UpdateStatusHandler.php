<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use InvalidArgumentException;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Exception\Happening\PollNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class UpdateStatusHandler
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

    public function handle(UpdateStatus $command)
    {
        $poll = $this->pollRepository->findById($command->pollId);
        if ($poll === null) {
            throw new PollNotFoundException(sprintf('No poll with id %d', $command->pollId));
        }

        if ($command->status === Poll::STATUS_PUBLISHED) {
            $poll->setPublished();
        } else if ($command->status === Poll::STATUS_HIDDEN) {
            $poll->setHidden();
        } else {
            throw new InvalidArgumentException(sprintf('Setting poll status to "%s" is not supported', $command->status));
        }

        $this->pollRepository->update($poll);

        switch ($command->status) {
            case Poll::STATUS_PUBLISHED:
                $this->notificationPublisher->publishNewPublishedPollNotification($poll);
                break;
            case Poll::STATUS_HIDDEN:
                $this->notificationPublisher->publishHiddenPollNotification($poll);
                break;
        }
    }
}
