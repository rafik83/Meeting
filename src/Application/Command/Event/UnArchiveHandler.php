<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Exception\Event\EventNotArchivedException;
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
        $this->eventRepository->set($command->event);
    }
}
