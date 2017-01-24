<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\SlotNotFoundException;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantViewQueryHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $slotRepository;

    /**
     * @var SlotAvailability
     */
    private $slotAvailability;

    /**
     * @var SlotView[]
     */
    private $slots = [];

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param MeetingSlotRepositoryInterface $slotRepository
     * @param SlotAvailability               $slotAvailability
     */
    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        SlotAvailability $slotAvailability
    ) {
        $this->participantRepository = $participantRepository;
        $this->slotRepository        = $slotRepository;
        $this->slotAvailability      = $slotAvailability;
    }

    /**
     * @param ParticipantViewQuery $query
     *
     * @return ParticipantView[]
     */
    public function handle(ParticipantViewQuery $query)
    {
        $participantViews = [];
        $this->indexSlotById($query);
        $slots = $this->slotRepository->getAvailableSlotByEvent($query->event);

        foreach ($query->sheets as $sheet) {
            $participants = $this->participantRepository->getParticipantsBySheetId($sheet->id);

            if (!empty($participants)) {
                foreach ($participants as $participant) {
                    $unavailabilitiesSlots = [];

                    foreach ($slots as $slot) {
                        if (!$this->slotAvailability->isAvailable($slot, $participant)->isAvailable()) {
                            $unavailabilitiesSlots[] = $this->getSlotViewFromSlot($slot);
                        }
                    }

                    $participantViews[] = new ParticipantView(
                        $participant->getId(),
                        $participant->getUser()->getAccount()->getCompleteName(),
                        $sheet,
                        $unavailabilitiesSlots
                    );
                }
            }
        }

        return $participantViews;
    }

    /**
     * @param MeetingSlot $slot
     *
     * @return SlotView
     *
     * @throws SlotNotFoundException
     */
    private function getSlotViewFromSlot(MeetingSlot $slot)
    {
        if (isset($this->slots[$slot->getId()])) {
            return $this->slots[$slot->getId()];
        }

        throw new SlotNotFoundException(sprintf('Slot with the id %s not found', $slot->getId()));
    }

    /**
     * @param ParticipantViewQuery $query
     */
    private function indexSlotById(ParticipantViewQuery $query)
    {
        foreach ($query->slots as $slot) {
            $this->slots[$slot->id] = $slot;
        }
    }
}
