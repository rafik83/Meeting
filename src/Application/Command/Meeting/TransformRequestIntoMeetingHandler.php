<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Exception\Meeting\NoSpotsAvailableForThisSlotAndMeetingException;
use Proximum\Vimeet\Domain\Meeting\MeetingParticipants;
use Proximum\Vimeet\Domain\Meeting\VisioGuesser;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Slot\SlotFilter;
use Proximum\Vimeet\Domain\Spot\AvailableSpots;

class TransformRequestIntoMeetingHandler
{
    /** @var AvailableSpots */
    private $availableSpots;

    /** @var MeetingParticipants */
    private $meetingParticipants;

    /** @var MeetingRepositoryInterface */
    public $meetingRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SlotFilter */
    private $slotFilter;

    /** @var VisioGuesser */
    private $visioGuesser;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        AvailableSpots $availableSpots,
        MeetingParticipants $meetingParticipants,
        MeetingRepositoryInterface $meetingRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SlotFilter $slotFilter,
        VisioGuesser $visioGuesser,
        \DateTimeInterface $dateTime
    ) {
        $this->availableSpots = $availableSpots;
        $this->meetingParticipants = $meetingParticipants;
        $this->meetingRepository = $meetingRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->slotFilter = $slotFilter;
        $this->visioGuesser = $visioGuesser;
        $this->dateTime = $dateTime;
    }

    public function handle(TransformRequestIntoMeeting $query): ?Meeting
    {
        $fromSheet = $query->request->getFromSheet();
        $toSheet = $query->request->getToSheet();

        $possibleParticipants = $this->getRequestPossibleParticipants($query->request);

        $fromParticipants = $toParticipants = [];
        $slot = $spot = null;
        foreach ($possibleParticipants['from'] as $fromParticipants) {
            foreach ($possibleParticipants['to'] as $toParticipants) {
                $participants = array_merge($fromParticipants, $toParticipants);
                $slots = $this->slotFilter->getFilteredSlots(
                    $this->meetingSlotRepository->findAvailableSlotsByParticipants($query->event, $participants)
                );

                if (empty($slots)) {
                    continue;
                }

                [$slot, $spot] = $this->getAvailableSlotAndSpot($slots, $fromSheet, $toSheet, $participants);

                if ($spot !== null) {
                    break 2;
                }
            }
        }

        if (empty($toParticipants) || empty($fromParticipants) || $slot === null || $spot === null) {
            return null;
        }

        $meeting = new Meeting(
            $query->request,
            $slot,
            $fromSheet,
            $fromParticipants,
            $toSheet,
            $toParticipants,
            $this->dateTime,
            $spot,
            $query->event,
            $query->blockedSpot,
            $query->blockedSlot,
            $query->createdBy
        );

        $this->meetingRepository->add($meeting);

        return $meeting;
    }

    private function getAvailableSlotAndSpot(array $slots, Sheet $fromSheet, Sheet $toSheet, array $participants): array
    {
        foreach ($slots as $slot) {
            $spot = $this->getAvailableSpot($slot, $fromSheet, $toSheet, $participants);

            if (null !== $spot) {
                return [$slot, $spot];
            }
        }

        return [];
    }

    private function getAvailableSpot(MeetingSlot $slot, Sheet $fromSheet, Sheet $toSheet, array $participants): ?Spot
    {
        try {
            return $this->availableSpots->getBySlot(
                $slot,
                $fromSheet,
                $toSheet,
                count($participants),
                $this->visioGuesser->isParticipantVisio($participants)
            );
        } catch (NoSpotsAvailableForThisSlotAndMeetingException $exception) {
            return null;
        }
    }

    private function getRequestPossibleParticipants(Meeting\Request $request): array
    {
        return [
            'from' => $this->getSheetPossibleParticipants($request, $request->getFromSheet()),
            'to' => $this->getSheetPossibleParticipants($request, $request->getToSheet()),
        ];
    }

    private function getSheetPossibleParticipants(Meeting\Request $request, Sheet $sheet): array
    {
        $requestParticipants = $this->meetingParticipants->getMeetingParticipants($request, $sheet);

        if (!empty($requestParticipants)) {
            return [$requestParticipants];
        }

        $possibleParticipants = [];
        foreach ($sheet->getParticipantsArray() as $participant) {
            $possibleParticipants[] = [$participant];
        }

        return $possibleParticipants;
    }
}
