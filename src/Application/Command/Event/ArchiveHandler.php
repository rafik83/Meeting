<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Exception\Event\DayNotDefinedException;
use Proximum\Vimeet\Domain\Exception\Event\EventAlreadyArchivedException;
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
     *
     * @return string
     */
    public function handle(Archive $command): string
    {
        if ($command->event->isArchived()) {
            throw new EventAlreadyArchivedException(
                sprintf('The event %s is already archived', $command->event->getId())
            );
        }

        $days = $command->event->getDays();
        $firstDay = reset($days);

        if (empty($days) || false === $firstDay) {
            throw new DayNotDefinedException(
                'The days of the event are not defined and therefore the suffix can not be added'
            );
        }

        $year = $firstDay->getStartTime()->format('Y');
        $domainSplit = explode('.', $command->event->getDomain());

        if (mb_substr($domainSplit[0], -4) !== $year) {
            $domainSplit[0] .= '-' . $year;
            $domain         = implode('.', $domainSplit);

            $command->event->setDomain($domain);
        }

        $command->event->archive();

        $this->eventRepository->set($command->event);

        return ArchiveUnArchive::ARCHIVE_DONE;
    }
}
