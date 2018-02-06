<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planner;

use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\MeetingSlotRepository;

class IndicatorCalculator
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingSlotRepository
     */
    private $slotRepository;

    /**
     * @var null|MeetingSlot[]
     */
    private $slots = null;

    /**
     * @var null|MeetingSlot[]
     */
    private $slotsUsable = null;

    /**
     * @var PlanningQuantityGuesser
     */
    private $planningQuantityGuesser;

    /**
     * @var SlotAvailability
     */
    private $slotAvailability;

    /**
     * @param RequestRepositoryInterface        $requestRepository
     * @param MeetingSlotRepository             $slotRepository
     * @param PlanningQuantityGuesser           $planningQuantityGuesser
     * @param SlotAvailability                  $slotAvailability
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepository $slotRepository,
        PlanningQuantityGuesser $planningQuantityGuesser,
        SlotAvailability $slotAvailability
    ) {
        $this->requestRepository        = $requestRepository;
        $this->slotRepository           = $slotRepository;
        $this->planningQuantityGuesser  = $planningQuantityGuesser;
        $this->slotAvailability         = $slotAvailability;
    }

    /**
     * Avoid calling the db for the number of slots by preloading it
     *
     * @param MeetingSlot[] $slots
     */
    public function preloadSlot(array $slots)
    {
        $this->slots = $slots;
    }

    /**
     * @param Sheet $sheet
     *
     * @return IndicatorView
     */
    public function getIndicator(Sheet $sheet)
    {
        if (null === $this->slots) {
            $this->slots = $this->slotRepository->findByEvent($sheet->getEvent());
        }

        $participantsCount       = $sheet->countParticipants();
        $pendingPropositionCount = $this->requestRepository->countPendingPropositionReceivedBySheet($sheet);
        $planningQuantity        = $this->planningQuantityGuesser->guess($sheet);
        $unavailabilities        = [];

        $meetingRequestsCount = $this
            ->requestRepository
            ->countSheetState($sheet, [
                'state'           => Request::STATE_APPROVED,
                'disabled'        => false,
                'isFromAttending' => true,
                'isToAttending'   => true,
            ]);

        if ($this->slotsUsable === null) {
            $slotUsables = [];

            foreach ($this->slots as $slot) {
                if ($this->slotAvailability->isUsable($slot)) {
                    $slotUsables[] = $slot;
                }
            }

            $this->slotsUsable = $slotUsables;
        }

        $massUnavaibilitiesCount = 0;

        foreach ($sheet->getParticipants()->toArray() as $participant) {
            foreach ($this->slotsUsable as $slotUsable) {
                $slotAvailability = $this->slotAvailability->getSlotAvailability($slotUsable, $participant);

                if (!$slotAvailability->isAvailable() && !$slotAvailability->isMeeting()) {
                    $unavailabilities[] = $slotUsable;
                }

                if ($slotAvailability->isMassUnavaibility()) {
                    $massUnavaibilitiesCount ++;
                }
            }
        }

        $unavailabilitiesCount = \count($unavailabilities);

        return new IndicatorView(
            \count($this->slotsUsable),
            $participantsCount,
            $unavailabilitiesCount,
            $planningQuantity,
            $meetingRequestsCount,
            $pendingPropositionCount,
            $massUnavaibilitiesCount
        );
    }
}
