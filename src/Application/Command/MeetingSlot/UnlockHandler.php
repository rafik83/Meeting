<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class UnlockHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    public $meetingSlotRepository;

    /**
     * UnlockHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param Unlock $command
     */
    public function handle(Unlock $command)
    {
        $this->meetingSlotRepository->set($command->meetingSlot->unlock());
    }
}
