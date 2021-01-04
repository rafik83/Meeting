<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;

class CleanOldVersionsCommandHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        VersionRepositoryInterface $versionRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->eventRepository = $eventRepository;
        $this->versionRepository = $versionRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(CleanOldVersionsCommand $command): void
    {
        $oldDate = (new \DateTime())->setTimestamp($this->dateTime->getTimestamp())->modify('-1month');
        $events = $this->eventRepository->findPastEventIds($oldDate);

        $this->versionRepository->removeVersionsOfEvents($events);
    }
}
