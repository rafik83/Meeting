<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\EmptySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\HappeningUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MassAssignmentUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MassUnavailabilitySlotView;
use Proximum\Vimeet\Application\View\Agenda\Slot\MeetingOnOtherSheetView;
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
            $query->masses,
            $query->massAssignments,
            $query->meetingOtherSheets
        );

        $slotViews = [];

        foreach ($slots as $slot) {
            if (false === $query->sheet->attend()) {
                $slotViews[] = new UnavailabilitySlotView($slot, SlotAvailability::UNAVAILABILITY);

                continue;
            }

            $slotAvailabilityView = $this->slotAvailability->getSlotAvailability($slot, $query->participant);

            if (SlotAvailability::HAPPENING_UNAVAILABILITY === $slotAvailabilityView->type) {
                $slotViews[] = new HappeningUnavailabilitySlotView($slot, $slotAvailabilityView->type);

                continue;
            }

            if (SlotAvailability::MEETING_UNAVAILABILITY === $slotAvailabilityView->type
                && null !== $slotAvailabilityView->meeting
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
                    $slotAvailabilityView->meeting->getRequest()->hasNoPreference($query->sheet),
                    $slotAvailabilityView->meeting->isBlockedSpot(),
                    $slotAvailabilityView->meeting->isBlockedSlot()
                );

                continue;
            }

            if (SlotAvailability::MEETING_ON_OTHER_SHEET === $slotAvailabilityView->type) {
                $slotViews[] = new MeetingOnOtherSheetView(
                    $slot,
                    $slotAvailabilityView->type,
                    $this->sheetInfoGuesser->guessSheetTitle($slotAvailabilityView->otherSheet),
                    $slotAvailabilityView->otherSheet->getId()
                );

                continue;
            }

            if (SlotAvailability::UNAVAILABILITY === $slotAvailabilityView->type) {
                $slotViews[] = new UnavailabilitySlotView($slot, $slotAvailabilityView->type);

                continue;
            }

            if (SlotAvailability::MASS_UNAVAILABILITY === $slotAvailabilityView->type) {
                if (null !== $slotAvailabilityView->massAssignment) {
                    $slotViews[] = new MassAssignmentUnavailabilitySlotView(
                        $slot,
                        SlotAvailability::MASS_ASSIGNMENT_UNAVAILABILITY,
                        $slotAvailabilityView->massAssignment->getId()
                    );
                } else {
                    $slotViews[] = new MassUnavailabilitySlotView($slot, $slotAvailabilityView->type);
                }

                continue;
            }

            if ($slot->isLocked()) {
                // Locked Slot is considered as a mass unavailability
                $slotViews[] = new MassUnavailabilitySlotView($slot, SlotAvailability::MASS_UNAVAILABILITY);

                continue;
            }

            $slotViews[] = new EmptySlotView($slot, $slotAvailabilityView->type);
        }

        return $slotViews;
    }
}
