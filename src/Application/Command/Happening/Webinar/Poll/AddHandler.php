<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Poll;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class AddHandler
{
    private PollRepositoryInterface $pollRepository;
    private DateTimeInterface $dateTime;
    private NotificationPublisherInterface $notificationPublisher;

    public function __construct(
        PollRepositoryInterface $pollRepository,
        DateTimeInterface $dateTime,
        NotificationPublisherInterface $notificationPublisher
    ) {
        $this->pollRepository = $pollRepository;
        $this->dateTime = $dateTime;
        $this->notificationPublisher = $notificationPublisher;
    }

    public function handle(Add $command)
    {
        $poll = new Poll(
            $command->happening,
            $command->user,
            $this->dateTime,
            $command->title,
            $command->choices,
            $command->multipleChoice
        );

        if ($command->publish) {
            $poll->setPublished();
        }

        $this->pollRepository->add($poll);

        if ($command->publish) {
            $this->notificationPublisher->publishNewPublishedPollNotification($poll);
        }
    }
}
