<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Exception\Slot\IsNotAllowedToRemoveSlotException;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class RemoveHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    public $meetingSlotRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param MeetingRepositoryInterface     $meetingRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->meetingRepository     = $meetingRepository;
    }

    /**
     * @param Remove $command
     *
     * @throws IsNotAllowedToRemoveSlotException
     */
    public function handle(Remove $command)
    {
        if ($this->meetingRepository->hasMeetingOnSlot($command->meetingSlot)) {
            $this->meetingSlotRepository->remove($command->meetingSlot);
        }
        throw new IsNotAllowedToRemoveSlotException('Slot already used by scheduled meetings');
    }
}
