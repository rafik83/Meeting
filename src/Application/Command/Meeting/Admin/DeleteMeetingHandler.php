<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class DeleteMeetingHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository, DelayedEventDispatcher $eventDispatcher)
    {
        $this->meetingRepository = $meetingRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param DeleteMeeting $deleteMeeting
     */
    public function handle(DeleteMeeting $deleteMeeting)
    {
        $this->meetingRepository->remove($deleteMeeting->meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent(
                [
                    $deleteMeeting->meeting->getFromSheet(),
                    $deleteMeeting->meeting->getToSheet(),
                ]
            )
        );
    }
}
