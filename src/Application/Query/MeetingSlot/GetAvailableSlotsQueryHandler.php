<?php

namespace Proximum\Vimeet\Application\Query\MeetingSlot;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class GetAvailableSlotsQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->spotRepository = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    public function handle(GetAvailableSlotsQuery $query): GetAvailableSlotsView
    {
        $sheetParticipants = $query->sheet->getParticipantsArray();
        $isSheetMultiParticipants = \count($sheetParticipants) > 1;

        if ($isSheetMultiParticipants) {
            // only used met sheet participants, sheet participants will be selected by user
            $participantsForAvailableSlots = $query->meeting->getMetParticipants($query->sheet);
        } else {
            // if there is only one participant in sheet, filter slots for all participants
            $participantsForAvailableSlots = $query->meeting->getAllParticipants();
        }

        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->meeting->getEvent(),
            $participantsForAvailableSlots,
            false,
            $isSheetMultiParticipants ? $query->meeting : null,
            $query->excludePastSlots
        );

        $availableSlots = [];

        foreach ($slots as $slot) {
            if (true === $this->spotRepository->hasSpotsForSlotAndParticipantsQuantity(
                    $slot,
                    $query->meeting->countParticipants(),
                    $query->meeting,
                    $query->meeting->getFromSheet(),
                    $query->meeting->getToSheet(),
                    $query->visio
                )) {
                $availableSlots[] = $slot;
            }
        }

        if (!$isSheetMultiParticipants) {
            return new GetAvailableSlotsView($availableSlots, []);
        }

        $currentSheetAvailableSlotIds = [];

        /** @var Participant $participant */
        foreach ($sheetParticipants as $participant) {
            $participantSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
                $query->meeting->getEvent(),
                [$participant],
                false,
                $query->meeting,
                $query->excludePastSlots
            );
            $currentSheetAvailableSlotIds[$participant->getId()] = array_map(
                fn (MeetingSlot $meetingSlot) => $meetingSlot->getId(), $participantSlots
            );
        }

        return new GetAvailableSlotsView($availableSlots, $currentSheetAvailableSlotIds);
    }
}
