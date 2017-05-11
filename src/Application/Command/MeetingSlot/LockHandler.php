<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToLockSlotException;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class LockHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    public $meetingSlotRepository;

    /**
     * LockHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param Lock $command
     *
     * @throws IsNotAllowedToLockSlotException
     */
    public function handle(Lock $command)
    {
        $meetingSlotWithMeetingIds = $this
            ->meetingSlotRepository
            ->findWithAtLeastOneMeetingByEvent($command->meetingSlot->getEvent());

        if (isset($meetingSlotWithMeetingIds[$command->meetingSlot->getId()])) {
            throw new IsNotAllowedToLockSlotException('Slot already used by scheduled meetings');
        }

        $this->meetingSlotRepository->set($command->meetingSlot->lock());
    }
}
