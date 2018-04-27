<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingUnParticipateEvent;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveMeetingHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * RemoveMeetingViewQueryHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepository
     * @param TranslatorInterface        $translator
     * @param DelayedEventDispatcher     $eventDispatcher
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        TranslatorInterface $translator,
        DelayedEventDispatcher $eventDispatcher
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->translator        = $translator;
        $this->eventDispatcher   = $eventDispatcher;
    }

    /**
     * @param RemoveMeeting $query
     *
     * @throws LockedException
     */
    public function handle(RemoveMeeting $query)
    {
        if ($query->meeting->isBlockedSlot()) {
            throw new LockedException($this->translator->trans(
                'flash.admin.meeting.remove.failed',
                [],
                'flashes',
                $query->user->getLocale()
            ));
        }

        $this->meetingRepository->remove($query->meeting);

        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent([
                $query->meeting->getFromSheet(),
                $query->meeting->getToSheet(),
            ])
        );

        foreach ($query->meeting->getAllParticipants() as $participant) {
            $this->eventDispatcher->dispatch(
                Events::MEETING_UN_PARTICIPATE,
                new MeetingUnParticipateEvent($participant)
            );
        }
    }
}
