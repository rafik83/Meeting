<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class UpdateSpotHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param UpdateSpot $updateSpot
     */
    public function handle(UpdateSpot $updateSpot)
    {
        $updateSpot->meeting->updateSpot($updateSpot->spot, $updateSpot->blockedSpot, $updateSpot->blockedSlot);
        $this->meetingRepository->set($updateSpot->meeting);
    }
}
