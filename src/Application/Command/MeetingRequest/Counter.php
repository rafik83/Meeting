<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class Counter
{
    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param RequestRepositoryInterface     $requestRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->requestRepository     = $requestRepository;
    }

    /**
     * Return the count of pending meeting request with available slot
     * in common with the sheet met and available slot begin date superior to current dateTime plus 10 minutes
     *
     * @param Sheet              $sheet
     * @param \DateTimeInterface $currentDatePlus10Minutes
     *
     * @return int
     */
    public function getCountAvailablePendingMeetingRequests(
        Sheet $sheet,
        \DateTimeInterface $currentDatePlus10Minutes
    ): int {
        $countAvailablePendingProposition = 0;
        $availableSlots                   = $this->meetingSlotRepository->findByIds($sheet->getAvailableSlots());
        $pendingPropositions              = $this->requestRepository->getPendingPropositionReceivedBySheet($sheet);

        foreach ($pendingPropositions as $pendingProposition) {
            $sheetMetAvailableSlots = $this->meetingSlotRepository->findByIds(
                $pendingProposition->getFromSheet()->getAvailableSlots()
            );

            foreach ($sheetMetAvailableSlots as $sheetMetAvailableSlot) {
                if (in_array($sheetMetAvailableSlot, $availableSlots, true)
                    && $sheetMetAvailableSlot->getBegin() >= $currentDatePlus10Minutes
                ) {
                    $countAvailablePendingProposition++;
                    break;
                }
            }
        }

        return $countAvailablePendingProposition;
    }
}
