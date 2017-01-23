<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class RemoveMeetingViewQueryHandler
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
     * RemoveMeetingViewQueryHandler constructor.
     *
     * @param MeetingRepositoryInterface $meetingRepository
     * @param TranslatorInterface        $translator
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        TranslatorInterface $translator
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->translator        = $translator;
    }

    /**
     * @param RemoveMeetingViewQuery $query
     *
     * @throws LockedException
     */
    public function handle(RemoveMeetingViewQuery $query)
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
    }
}
