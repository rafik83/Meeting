<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Event\Index;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class IndexHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        JobQueueInterface $jobQueue
    ) {
        $this->eventRepository = $eventRepository;
        $this->jobQueue = $jobQueue;
    }

    public function handle(): void
    {
        $events = $this->eventRepository->getEventsOrderByIdDesc();

        foreach ($events as $event) {
            $this->jobQueue->indexSheetsByEvent($event, false);
        }
    }
}
