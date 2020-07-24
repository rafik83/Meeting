<?php

namespace Proximum\Vimeet\Domain\Planner;

use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class IndicatorCalculator
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var PlanningQuantityGuesser */
    private $planningQuantityGuesser;

    /** @var SlotAvailability */
    private $slotAvailability;

    /** @var null|MeetingSlot[] cached Event slots */
    private $slots = null;

    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        PlanningQuantityGuesser $planningQuantityGuesser,
        SlotAvailability $slotAvailability
    ) {
        $this->requestRepository = $requestRepository;
        $this->slotRepository = $slotRepository;
        $this->planningQuantityGuesser = $planningQuantityGuesser;
        $this->slotAvailability = $slotAvailability;
    }

    public function getIndicator(Sheet $sheet): IndicatorView
    {
        $participantsCount = $sheet->countParticipants();
        $pendingPropositionCount = $this->requestRepository->countPendingPropositionReceivedBySheet($sheet);
        $planningQuantity = $this->planningQuantityGuesser->guess($sheet);
        $unavailabilities = [];

        $meetingRequestsCount = $this
            ->requestRepository
            ->countSheetState($sheet, [
                'state'           => Request::STATE_APPROVED,
                'disabled'        => false,
                'isFromAttending' => true,
                'isToAttending'   => true,
            ]);

        $usableSlots = $this->getUsableSlots($sheet);

        $massUnavaibilitiesCount = 0;

        foreach ($sheet->getParticipantsArray() as $participant) {
            foreach ($usableSlots as $usableSlot) {
                $slotAvailability = $this->slotAvailability->getSlotAvailability($usableSlot, $participant);

                if (!$slotAvailability->isAvailable() && !$slotAvailability->isMeeting()) {
                    $unavailabilities[] = $usableSlot;
                }

                if ($slotAvailability->isMassUnavaibility()) {
                    ++$massUnavaibilitiesCount;
                }
            }
        }

        $unavailabilitiesCount = \count($unavailabilities);

        return new IndicatorView(
            \count($usableSlots),
            $participantsCount,
            $unavailabilitiesCount,
            $planningQuantity,
            $meetingRequestsCount,
            $pendingPropositionCount,
            $massUnavaibilitiesCount,
            $sheet->getType()->getNumberOfMeetingsPerPlanning(),
            $sheet->getType()->getNumberMaxOfMeetingsPerSheet()
        );
    }

    /**
     * @return MeetingSlot[]
     */
    private function getUsableSlots(Sheet $sheet): array
    {
        if (null === $this->slots) {
            $this->slots = $this->slotRepository->findByEvent($sheet->getEvent());
        }

        $usableSlots = [];

        foreach ($this->slots as $slot) {
            if ($this->slotAvailability->isUsable($sheet, $slot)) {
                $usableSlots[] = $slot;
            }
        }

        return $usableSlots;
    }
}
