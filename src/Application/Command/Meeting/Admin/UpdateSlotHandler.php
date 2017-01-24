<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\MeetingUpdateSlotViewQueryHandler;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class UpdateSlotHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingUpdateSlotViewQueryHandler */
    private $meetingUpdateSlotViewQueryHandler;

    /**
     * @param MeetingRepositoryInterface        $meetingRepository
     * @param SpotRepositoryInterface           $spotRepository
     * @param MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SpotRepositoryInterface $spotRepository,
        MeetingUpdateSlotViewQueryHandler $meetingUpdateSlotViewQueryHandler
    ) {
        $this->meetingRepository     = $meetingRepository;
        $this->spotRepository        = $spotRepository;
        $this->meetingUpdateSlotViewQueryHandler = $meetingUpdateSlotViewQueryHandler;
    }

    /**
     * @param UpdateSlot $updateSlot
     *
     * @throws \Exception
     */
    public function handle(UpdateSlot $updateSlot)
    {
        $meetingUpdateSlotView = $this->meetingUpdateSlotViewQueryHandler->handle(
            new MeetingUpdateSlotViewQuery($updateSlot->meeting)
        );

        if (false === in_array($updateSlot->slot->getId(), $meetingUpdateSlotView->availableSlotsId)) {
            throw new \Exception('slot not available');
        }

        $spots = $this->spotRepository->getSpotsForSlotAndParticipantsQuantity(
            $updateSlot->slot,
            $updateSlot->meeting->countParticipants(),
            $updateSlot->meeting
        );

        if (0 === count($spots)) {
            throw new \Exception('No spot is available');
        }

        $updateSlot->meeting->updateSlotAndSpot($updateSlot->slot, reset($spots));
        $this->meetingRepository->set($updateSlot->meeting);
    }
}
