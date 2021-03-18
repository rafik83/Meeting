<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\Exception\Planner\ParticipantNotFoundException;
use Proximum\Vimeet\Application\Exception\Planner\SheetNotFoundException;
use Proximum\Vimeet\Application\View\Planner\MeetingView;
use Proximum\Vimeet\Application\View\Planner\ParticipantView;
use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Application\View\Planner\SlotView;
use Proximum\Vimeet\Application\View\Planner\SpotView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class MeetingViewQueryHandler
{
    /** @var SheetView[] */
    private $sheets = [];

    /** @var ParticipantView[] */
    private $participants;

    /** @var array */
    private $dispatch = [];

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var Request[] */
    private $requests = [];

    /** @var int */
    private $recursiveDepth = 0;

    /** @var SpotView[] */
    private $spots;

    /** @var SlotView[] */
    private $slots;

    /** @var array */
    private $participantsMatched = [];

    /**
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(RequestRepositoryInterface $requestRepository)
    {
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param MeetingViewQuery $query
     *
     * @return MeetingView[]
     */
    public function handle(MeetingViewQuery $query)
    {
        $this->setUp($query);

        $requestsWithMeeting = [];
        $requestsWithoutMeeting = [];

        foreach ($this->requests as $request) {
            if ($request->hasMeeting()) {
                $requestsWithMeeting[$request->getId()] = $request;

                continue;
            }

            $requestsWithoutMeeting[$request->getId()] = $request;
        }

        return array_merge(
            $this->handleRequests($query, $requestsWithMeeting),
            $this->handleRequests($query, $requestsWithoutMeeting)
        );
    }

    private function handleRequests(MeetingViewQuery $query, array $requests): array
    {
        $meetingViews = [];

        foreach ($requests as $request) {
            try {
                $sheetsList = [
                    $this->getSheetById($request->getFromSheet()->getId()),
                    $this->getSheetById($request->getToSheet()->getId()),
                ];

                $participantsList = $this->getParticipantsList($query, $request, $request->getMeeting());

                if (empty($participantsList)) {
                    continue;
                }

                $tempParticipantMatched = [];

                foreach ($participantsList['fromParticipant'] as $fromParticipant) {
                    foreach ($participantsList['toParticipant'] as $toParticipant) {
                        $tempParticipantMatched[$fromParticipant->userId][$toParticipant->userId] = $toParticipant->userId;
                        $tempParticipantMatched[$toParticipant->userId][$fromParticipant->userId] = $fromParticipant->userId;

                        if (isset($this->participantsMatched[$fromParticipant->userId][$toParticipant->userId]) ||
                            isset($this->participantsMatched[$toParticipant->userId][$fromParticipant->userId])
                        ) {
                            continue 3;
                        }
                    }
                }

                $participantsList = array_merge(
                    $participantsList['fromParticipant'],
                    $participantsList['toParticipant']
                );

                $meetingView = new MeetingView(
                    $request->getId(),
                    $sheetsList,
                    $participantsList,
                    $this->isVisio($participantsList)
                );

                if (!$query->isSolutionFromScratch() && $request->hasMeeting()) {
                    $meeting = $request->getMeeting();

                    if (null !== $meeting) {
                        $meetingView->slot = $this->getSlotById($meeting->getSlot()->getId());
                        $meetingView->spot = $this->getSpotById($meeting->getSpot()->getId());

                        if ($meeting->isBlockedSlot()) {
                            $meetingView->lockedSlot  = $this->getSlotById($meeting->getSlot()->getId());
                            $meetingView->isBlockedSlot = true;
                        }

                        if ($meeting->isBlockedSpot()) {
                            $meetingView->lockedSpot  = $this->getSpotById($meeting->getSpot()->getId());
                            $meetingView->isBlockedSpot = true;
                        }

                        if ($query->isSolutionLocked()) {
                            $meetingView->lockedSlot = $this->getSlotById($meeting->getSlot()->getId());
                            $meetingView->lockedSpot = $this->getSpotById($meeting->getSpot()->getId());
                        }
                    }
                }

                foreach ($tempParticipantMatched as $key => $ids) {
                    if (!isset($this->participantsMatched[$key])) {
                        $this->participantsMatched[$key] = [];
                    }

                    foreach($ids as $id) {
                        $this->participantsMatched[$key][$id] = $meetingView;
                    }
                }

                $meetingViews[] = $meetingView;
            } catch (SheetNotFoundException $exception) {
                // In case of a sheet not in catalog but with meeting request
            }
        }

        return $meetingViews;
    }

    /**
     * Reset the recursive depth
     */
    private function resetRecursiveDepth()
    {
        $this->recursiveDepth = 0;
    }

    /**
     * @param Participant[] $participants
     *
     * @return ParticipantView[]
     */
    private function getParticipantViews(array $participants): array
    {
        $participantViews = [];

        foreach ($participants as $participant) {
            $participantViews[] = $this->getParticipantById($participant->getId());
        }

        return $participantViews;
    }

    /**
     * @param Sheet         $sheet
     * @param Participant[] $meetingParticipants
     * @param Participant[] $requestParticipants
     *
     * @return ParticipantView[]
     */
    private function getRequestParticipants(Sheet $sheet, array $meetingParticipants, array $requestParticipants): array
    {
        $participantViews = [];

        if ($sheet->getType()->areAllSheetParticipantsAssignedToMeeting()) {
            if ($sheet->hasLinkedSheets()) {
                return $this->getParticipantViews($sheet->getLinkedSheetsParticipants());
            }

            return $this->getParticipantViews($sheet->getParticipantsArray());
        }

        if (count($meetingParticipants)) {
            return $this->getParticipantViews($meetingParticipants);
        }

        if (count($requestParticipants)) {
            foreach ($requestParticipants as $participant) {
                $participantViews[] = $this->getParticipantById($participant->getId());
            }

            return $participantViews;
        }

        // No preference and if sheet has only one participant
        if (1 === $sheet->countParticipants()) {
            foreach ($sheet->getParticipantsArray() as $participant) {
                $participantViews[] = $this->getParticipantById($participant->getId());
            }

            return $participantViews;
        }

        // Assign a random participant
        $this->resetRecursiveDepth();
        $participantViews[] = $this->getParticipantOfSheet($sheet);

        return $participantViews;
    }

    /**
     * @param MeetingViewQuery $query
     * @param Request          $request
     * @param Meeting|null     $meeting
     *
     * @return ParticipantView[]
     */
    private function getParticipantsList(MeetingViewQuery $query, Request $request, Meeting $meeting = null): array
    {
        $hasMeetingAndSolutionNotFromScratch = null !== $meeting && !$query->isSolutionFromScratch();

        return [
            'fromParticipant' => $this->getRequestParticipants(
                $request->getFromSheet(),
                $hasMeetingAndSolutionNotFromScratch ? $meeting->getFromParticipantsArray() : [],
                !$hasMeetingAndSolutionNotFromScratch && $request->hasFromParticipants()
                    ? $request->getFromParticipantsArray()
                    : []
            ),
            'toParticipant' => $this->getRequestParticipants(
                $request->getToSheet(),
                $hasMeetingAndSolutionNotFromScratch ? $meeting->getToParticipantsArray() : [],
                !$hasMeetingAndSolutionNotFromScratch && $request->hasToParticipants()
                    ? $request->getToParticipantsArray()
                    : []
            ),
        ];
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function setUp(MeetingViewQuery $query)
    {
        $this->indexById($query);
        $this->requests = $this->requestRepository->getAllAcceptedByEvent($query->event);
        $this->countRequestBySheet();
        $this->calculateAssignation();
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexById(MeetingViewQuery $query)
    {
        $this->indexSheetsById($query);
        $this->indexSpotsById($query);
        $this->indexSlotsById($query);
        $this->indexParticipantsById($query);
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexSpotsById(MeetingViewQuery $query)
    {
        foreach ($query->spots as $spot) {
            $this->spots[$spot->id] = $spot;
        }
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexSlotsById(MeetingViewQuery $query)
    {
        foreach ($query->slots as $slot) {
            $this->slots[$slot->id] = $slot;
        }
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexParticipantsById(MeetingViewQuery $query)
    {
        $slotTotal = count($query->slots);

        foreach ($query->participants as $participant) {
            // Index participant by id
            $this->participants[$participant->id] = $participant;

            // Calculate percentage for dispatch
            $usersSlots = $slotTotal - count($participant->unavailabilityList);

            if ($usersSlots < 0) {
                $usersSlots = 0;
            }

            $percentage = ($usersSlots * 100) / $slotTotal;

            $this->dispatch[$participant->sheet->id]['participant'][$participant->id]['ppu'] = $percentage;
        }
    }

    /**
     * @param MeetingViewQuery $query
     */
    private function indexSheetsById(MeetingViewQuery $query)
    {
        foreach ($query->sheets as $sheet) {
            $this->sheets[$sheet->id] = $sheet;
        }
    }

    /**
     * @param int $id
     *
     * @throws SheetNotFoundException
     *
     * @return SheetView
     */
    private function getSheetById($id)
    {
        if (isset($this->sheets[$id])) {
            return $this->sheets[$id];
        }

        throw new SheetNotFoundException(sprintf('Sheet of id %s was not found', $id));
    }

    /**
     * @param int $id
     *
     * @throws ParticipantNotFoundException
     *
     * @return ParticipantView
     */
    private function getParticipantById($id)
    {
        if (isset($this->participants[$id])) {
            return $this->participants[$id];
        }

        throw new ParticipantNotFoundException(sprintf('Participant of id %s was not found', $id));
    }

    /**
     * Check if at least a participant is Visio
     *
     * @param ParticipantView[] $participantList
     *
     * @return bool
     */
    private function isVisio(array &$participantList)
    {
        foreach ($participantList as $participant) {
            if ($participant->isVisio) {
                return true;
            }
        }

        return false;
    }

    /**
     * Count the request with no preference by sheet
     */
    private function countRequestBySheet()
    {
        foreach ($this->requests as $request) {
            if (!$request->hasFromParticipants()) {
                $fromSheetId = $request->getFromSheet()->getId();

                if (!isset($this->dispatch[$fromSheetId]['request'])) {
                    $this->dispatch[$fromSheetId]['request'] = 1;
                } else {
                    ++$this->dispatch[$fromSheetId]['request'];
                }
            }

            if (!$request->hasToParticipants()) {
                $toSheetId = $request->getToSheet()->getId();

                if (!isset($this->dispatch[$toSheetId]['request'])) {
                    $this->dispatch[$toSheetId]['request'] = 1;
                } else {
                    ++$this->dispatch[$toSheetId]['request'];
                }
            }
        }
    }

    /**
     * This function has the goal of calculate and set the PPU and NRAP of each participant
     */
    private function calculateAssignation()
    {
        /*
         * Calculation rule:
         *
         * (slot where the user is available) * 100 / (number of slots of the event) = percentage of presence of the user (PPU)
         *
         *
         * PPU * total of request / (∑ PPU) = number of request to assign to this participant (NRAP)
         */

        foreach ($this->sheets as $sheet) {
            $sumPPU = array_reduce(
                $this->dispatch[$sheet->id]['participant'],
                function ($carry, $participantInfo) {
                    return $carry + $participantInfo['ppu'];
                },
                0
            );

            foreach ($this->dispatch[$sheet->id]['participant'] as $key => $participantInfo) {
                // Avoid divided by 0
                if (0 === $sumPPU) {
                    $this->dispatch[$sheet->id]['participant'][$key]['nrap']     = 0;
                    $this->dispatch[$sheet->id]['participant'][$key]['assigned'] = 0;
                } else {
                    if (!isset($this->dispatch[$sheet->id]['request'])) {
                        $this->dispatch[$sheet->id]['participant'][$key]['nrap'] = 0;
                    } else {
                        $this->dispatch[$sheet->id]['participant'][$key]['nrap'] = ($this->dispatch[$sheet->id]['participant'][$key]['ppu'] * $this->dispatch[$sheet->id]['request']) / $sumPPU;
                    }

                    // set the number of request assigned to 0
                    $this->dispatch[$sheet->id]['participant'][$key]['assigned'] = 0;
                }
            }
        }
    }

    /**
     * @param Sheet $sheet
     *
     * @throws ParticipantNotFoundException
     *
     * @return ParticipantView
     */
    private function getParticipantOfSheet(Sheet $sheet)
    {
        ++$this->recursiveDepth;
        $randomParticipantKey = array_rand($this->dispatch[$sheet->getId()]['participant']);
        $participantInfo      = $this->dispatch[$sheet->getId()]['participant'][$randomParticipantKey];

        // Avoid taking the participant with 0 nrap as >= can take it
        if (0 !== $participantInfo['nrap']
            && $participantInfo['nrap'] >= $participantInfo['assigned']
            || $this->recursiveDepth > 200
        ) {
            // Increment the number of meeting assigned to the participant
            ++$this->dispatch[$sheet->getId()]['participant'][$randomParticipantKey]['assigned'];

            return $this->getParticipantById($randomParticipantKey);
        }

        // Otherwise, recursivity
        return $this->getParticipantOfSheet($sheet);
    }

    /**
     * @param int $spotId
     *
     * @return null|SpotView
     */
    private function getSpotById($spotId)
    {
        return isset($this->spots[$spotId]) ? $this->spots[$spotId] : null;
    }

    /**
     * @param int $slotId
     *
     * @return null|SlotView
     */
    private function getSlotById($slotId)
    {
        return isset($this->slots[$slotId]) ? $this->slots[$slotId] : null;
    }
}
