<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Exception\Event\EventNotArchivedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class UnArchiveHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param UnArchive $command
     *
     * @throws EventNotArchivedException
     */
    public function handle(UnArchive $command)
    {
        if (!$command->event->isArchived()) {
            throw new EventNotArchivedException(
                sprintf('The event %s is not archived and can not be unarchive', $command->event->getId())
            );
        }

        $command->event->unArchive();

        $this->unsetYearOnDomain($command->event);

        $this->eventRepository->set($command->event);
    }

    /**
     * @param Event $event
     */
    private function unsetYearOnDomain(Event $event)
    {
        $days = $event->getDays();
        $firstDay = reset($days);

        if (false === $firstDay) {
            return;
        }

        $year = $firstDay->getStartTime()->format('Y');
        $domainSplit = explode('.', $event->getDomain(), 2);

        if (mb_substr($domainSplit[0], -5) === '-' . $year) {
            $domainSplit[0] = mb_substr($domainSplit[0], 0, -5);
            $domain         = implode('.', $domainSplit);

            $event->setDomain($domain);
        }
    }
}
