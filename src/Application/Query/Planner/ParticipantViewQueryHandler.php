<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\SlotNotFoundException;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Domain\Meeting\Slot\SlotAvailability;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Participant\IsParticipantVisio;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantViewQueryHandler
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var SlotAvailability */
    private $slotAvailability;

    /** @var SlotView[] */
    private $slots = [];

    /** @var Participant[] */
    private $participants = [];

    /** @var IsParticipantVisio */
    private $isParticipantVisio;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        SlotAvailability $slotAvailability,
        IsParticipantVisio $isParticipantVisio
    ) {
        $this->participantRepository = $participantRepository;
        $this->slotRepository        = $slotRepository;
        $this->slotAvailability      = $slotAvailability;
        $this->isParticipantVisio = $isParticipantVisio;
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

        $this->setUpParticipants($query);

        foreach ($query->sheets as $sheet) {
            $participants = [];

            if (isset($this->participants[$sheet->id])) {
                $participants = $this->participants[$sheet->id];
            }

            if (!empty($participants)) {
                /** @var Participant $participant */
                foreach ($participants as $participant) {
                    $unavailabilitiesSlots = [];

                    foreach ($slots as $slot) {
                        $slotView = $this->slotAvailability->getSlotAvailability($slot, $participant);

                        // Avoid set as unavailability a meeting
                        if (!$slotView->isAvailable() && !$slotView->isMeeting()) {
                            $unavailabilitiesSlots[] = $this->getSlotViewFromSlot($slot);
                        }
                    }

                    $participantViews[] = new ParticipantView(
                        $participant->getId(),
                        $participant->getUser()->getId(),
                        $participant->getUser()->getAccount()->getCompleteName(),
                        $sheet,
                        $unavailabilitiesSlots,
                        $this->isParticipantVisio->isSatisfiedBy($participant)
                    );
                }
            }
        }

        return $participantViews;
    }

    /**
     * @param ParticipantViewQuery $query
     */
    private function setUpParticipants(ParticipantViewQuery $query)
    {
        $participants = $this->participantRepository->getParticipantsBySheetIds(
            array_map(function (SheetView $sheetView) {
                return $sheetView->id;
            }, $query->sheets)
        );

        foreach ($participants as $participant) {
            $this->participants[$participant->getSheet()->getId()][] = $participant;
        }
    }

    /**
     * @param MeetingSlot $slot
     *
     * @throws SlotNotFoundException
     *
     * @return SlotView
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
