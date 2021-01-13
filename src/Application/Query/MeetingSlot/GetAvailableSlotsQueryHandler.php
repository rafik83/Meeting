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
        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
            $query->meeting->getEvent(),
            $query->meeting->getMetParticipants($query->sheet),
            false
        );

        $currentSheetAvailableSlotIds = [];
        /** @var Participant $participant */
        foreach ($query->sheet->getParticipantsArray() as $participant) {
            $participantSlots = $this->meetingSlotRepository->findAvailableSlotsByParticipants(
                $query->meeting->getEvent(),
                [$participant],
                false
            );
            $currentSheetAvailableSlotIds[$participant->getId()] = array_map(
                fn (MeetingSlot $meetingSlot) => $meetingSlot->getId(), $participantSlots
            );
        }

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

        return new GetAvailableSlotsView($availableSlots, $currentSheetAvailableSlotIds);
    }
}
