<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Exception\Meeting\MeetingIsBlockedSpotException;
use Proximum\Vimeet\Application\Exception\Meeting\SpotNotAvailableForThisMeetingException;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateSpotHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SpotRepositoryInterface    $spotRepository
     */
    public function __construct(MeetingRepositoryInterface $meetingRepository, SpotRepositoryInterface $spotRepository)
    {
        $this->meetingRepository = $meetingRepository;
        $this->spotRepository    = $spotRepository;
    }

    /**
     * @param UpdateSpot $updateSpot
     *
     * @throws MeetingIsBlockedSpotException
     * @throws SpotNotAvailableForThisMeetingException
     */
    public function handle(UpdateSpot $updateSpot)
    {
        $isMeetingSpotChanged     = $updateSpot->spot !== $updateSpot->meeting->getSpot();
        $isMeetingSpotStayBlocked = $updateSpot->meeting->isBlockedSpot() && $updateSpot->isBlockedSpot();

        if (true === $isMeetingSpotChanged && true === $isMeetingSpotStayBlocked) {
            throw new MeetingIsBlockedSpotException();
        }

        if (false === \in_array(
            $updateSpot->spot,
            $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
                $updateSpot->meeting->getSlot(),
                $updateSpot->meeting->countParticipants(),
                $updateSpot->meeting,
                null,
                null,
                $updateSpot->visio
            )
        )) {
            throw new SpotNotAvailableForThisMeetingException();
        }

        $updateSpot->meeting->updateSpot($updateSpot->spot, $updateSpot->blockedSpot, $updateSpot->blockedSlot);
        $updateSpot->meeting->resetStatus();
        $this->meetingRepository->set($updateSpot->meeting);
    }
}
