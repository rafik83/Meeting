<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\EmptySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\HappeningUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MassUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\UnavailabilitySlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class SlotViewQueryHandler
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var SlotAvailability
     */
    private $slotAvailability;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * SlotViewQueryHandler constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param SlotAvailability               $slotAvailability
     * @param SheetInfoGuesser               $sheetInfoGuesser
     */
    public function __construct(
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SlotAvailability $slotAvailability,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotAvailability      = $slotAvailability;
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
    }

    /**
     * @param SlotViewQuery $query
     *
     * @return AbstractSlotView[]
     */
    public function handle(SlotViewQuery $query)
    {
        $slots = $this->meetingSlotRepository->findByEventAndDay($query->event, $query->day);

        $this->slotAvailability->preload(
            $query->happenings,
            $query->meetings,
            $query->unavailabilities,
            $query->masses
        );

        $slotViews = [];

        foreach ($slots as $slot) {
            $slotAvailabilityView = $this->slotAvailability->isAvailable($slot, $query->participant);

            if ($slotAvailabilityView->type === SlotAvailability::HAPPENING_UNAVAILABILITY) {
                $slotViews[] = new HappeningUnavailabilitySlotView($slot, $slotAvailabilityView->type);
                continue;
            }

            if ($slotAvailabilityView->type === SlotAvailability::MEETING_UNAVAILABILITY
                && $slotAvailabilityView->meeting !== null
            ) {
                $sheetMet = $slotAvailabilityView->meeting->getSheetMet($query->sheet);

                $slotViews[] = new MeetingSlotView(
                    $slot,
                    $slotAvailabilityView->type,
                    $slotAvailabilityView->meeting->getSpot()->getId(),
                    $slotAvailabilityView->meeting->getSpot()->getReference(),
                    $sheetMet->getId(),
                    $this->sheetInfoGuesser->guessSheetTitle($sheetMet),
                    $slotAvailabilityView->meeting->getId(),
                    $slotAvailabilityView->meeting->getRequest()->hasNoPreference($query->sheet)
                );
                continue;
            }

            if ($slotAvailabilityView->type === SlotAvailability::UNAVAILABILITY) {
                $slotViews[] = new UnavailabilitySlotView($slot, $slotAvailabilityView->type);
                continue;
            }

            if ($slotAvailabilityView->type === SlotAvailability::MASS_UNAVAILABILITY) {
                $slotViews[] = new MassUnavailabilitySlotView($slot, $slotAvailabilityView->type);
                continue;
            }

            $slotViews[] = new EmptySlotView($slot, $slotAvailabilityView->type);
        }

        return $slotViews;
    }
}
