<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Sheet\Aggregate;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AvailableSlotCalculator
{
    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param MeetingSlotRepositoryInterface $slotRepository
     * @param SheetRepositoryInterface       $sheetRepository
     */
    public function __construct(
        MeetingSlotRepositoryInterface $slotRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->slotRepository = $slotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Sheet $sheet
     */
    public function calculateAvailableSlotForSheet(Sheet $sheet)
    {
        $slots = $this->slotRepository->findAvailableSlotsByParticipants(
            $sheet->getEvent(),
            $sheet->getParticipants()->toArray()
        );

        $availableSlots = [];

        foreach ($slots as $slot) {
            $availableSlots[] = $slot->getId();
        }

        $sheet->setAvailableSlots($availableSlots);

        $this->sheetRepository->set($sheet);
    }
}
