<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\View\Agenda\Slot\SpotSlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\SpotUnavailability;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotUnavailabilityRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var SpotUnavailabilityRepositoryInterface
     */
    private $spotUnavailabilityRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * SlotViewQueryHandler constructor.
     *
     * @param MeetingRepositoryInterface            $meetingRepository
     * @param MeetingSlotRepositoryInterface        $meetingSlotRepository
     * @param SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SpotUnavailabilityRepositoryInterface $spotUnavailabilityRepository
    ) {
        $this->meetingSlotRepository        = $meetingSlotRepository;
        $this->spotUnavailabilityRepository = $spotUnavailabilityRepository;
        $this->meetingRepository            = $meetingRepository;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return SpotSlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $spotUnavailabilities = $this->spotUnavailabilityRepository->findBySpot($query->spot);

        $slots     = $this->meetingSlotRepository->findByEventAndDay($query->event, $query->day);
        $slotViews = [];

        foreach ($slots as $slot) {
            $meetings      = $this->meetingRepository->findBySpotAndSlot($query->spot, $slot);
            $isUnavailable = $this->hasUnavailability($slot, $spotUnavailabilities);

            if ($isUnavailable) {
                $type = SlotAvailability::UNAVAILABILITY;
            } elseif (count($meetings) > 0) {
                $type = SlotAvailability::MEETING_UNAVAILABILITY;
            } else {
                $type = SlotAvailability::SLOT_AVAILABLE;
            }

            $slotView = new SpotSlotView(
                $slot,
                $type,
                []
            );

            $slotViews[] = $slotView;
        }

        return $slotViews;
    }

    /**
     * @param MeetingSlot $slot
     * @param array       $spotUnavailabilities
     *
     * @return bool
     */
    private function hasUnavailability(MeetingSlot $slot, array $spotUnavailabilities)
    {
        /** @var SpotUnavailability $spotUnavailability */
        foreach ($spotUnavailabilities as $spotUnavailability) {
            if ($spotUnavailability->getSlot()->getId() === $slot->getId()) {
                return true;
            }
        }

        return false;
    }
}
