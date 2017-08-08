<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class LastEventParticipation
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param EventRepositoryInterface $eventRepository
     * @param \DateTimeInterface       $dateTime
     */
    public function __construct(EventRepositoryInterface $eventRepository, \DateTimeInterface $dateTime)
    {
        $this->dateTime = $dateTime;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param User  $user
     * @param Event $currentEvent
     *
     * @return null|Event
     */
    public function getLastEvent(User $user, Event $currentEvent): ?Event
    {
        // TODO: currentEvent getDuplicateFrom

        $lastEvent = $this->eventRepository->getLastEventParticipation($user, $currentEvent);

        return $lastEvent;
    }
}
