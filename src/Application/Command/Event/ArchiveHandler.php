<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Exception\Event\EventAlreadyArchivedException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class ArchiveHandler
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
     * @param Archive $command
     *
     * @throws DayNotDefinedException
     * @throws EventAlreadyArchivedException
     */
    public function handle(Archive $command)
    {
        if ($command->event->isArchived()) {
            throw new EventAlreadyArchivedException(
                sprintf('The event %s is already archived', $command->event->getId())
            );
        }

        $this->setDomainWithYear($command->event);
        $command->event->archive();

        $this->eventRepository->set($command->event);
    }

    /**
     * @param Event $event
     *
     * @throws DayNotDefinedException
     */
    private function setDomainWithYear(Event $event)
    {
        $days = $event->getDays();
        $firstDay = reset($days);

        if (false === $firstDay) {
            throw new DayNotDefinedException(
                'The days of the event are not defined and therefore the suffix can not be added'
            );
        }

        $year = $firstDay->getStartTime()->format('Y');
        $domainSplit = explode('.', $event->getDomain(), 2);

        if (mb_substr($domainSplit[0], -4) !== $year) {
            $domainSplit[0] .= '-' . $year;
            $domain         = implode('.', $domainSplit);

            $event->setDomain($domain);
        }
    }
}
