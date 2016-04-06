<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class RequestsToMeetingsHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @param SheetRepositoryInterface       $sheetRepository
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->sheetRepository       = $sheetRepository;
        $this->meetingRepository     = $meetingRepository;
        $this->requestRepository     = $requestRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param RequestsToMeetings $requestsToMeetings
     */
    public function handle(RequestsToMeetings $requestsToMeetings)
    {
        $event = $requestsToMeetings->event;

        // Delete all previous meetings for this event
        $this->meetingRepository->deleteAll($event);

        // Get event sheets
        $sheets = $this->sheetRepository->getByEvent($event);

        // Get event slots
        $eventSlots = $this->meetingSlotRepository->findByEvent($event);

        $sheetSlots    = [];
        $sheetRequests = [];

        foreach ($sheets as $sheet) {
            $ids = array_map(function (Participant $participant) {
                return $participant->getId();
            }, $sheet->getParticipants()->toArray());

            $sheetSlots[$sheet->getId()]    = $this->meetingSlotRepository->findAvailableSlotIdByParticipantsIds($ids);
            $sheetRequests[$sheet->getId()] = $this->requestRepository->getApprovedRequestSentBySheet($sheet);
        }

        while (true) {
            $createdMeeting = false;

            foreach ($sheets as $sheet) {
                if (!isset($sheetRequests[$sheet->getId()]) && !count($sheetRequests[$sheet->getId()])) {
                    continue;
                }

                if (!count($sheetSlots[$sheet->getId()])) {
                    continue;
                }

                /** @var Request $request * */
                $request = array_shift($sheetRequests[$sheet->getId()]);

                if (null === $request) {
                    continue;
                }

                // Get matched slots between the two sheets
                $matchedSlots = array_intersect(
                    $sheetSlots[$request->getFromSheet()->getId()],
                    $sheetSlots[$request->getToSheet()->getId()]
                );

                $slotId = array_shift($matchedSlots);

                if (null === $slotId) {
                    continue;
                }

                // Create the meeting
                $meeting = new Meeting(
                    $eventSlots[$slotId],
                    $request->getFromSheet(),
                    $request->getFromParticipants()->toArray(),
                    $request->getToSheet(),
                    $request->getToParticipants()->toArray(),
                    $requestsToMeetings->createdAt
                );

                $this->meetingRepository->add($meeting);

                // Attach the meeting to the request
                $request->setMeeting($meeting);
                $this->requestRepository->set($request);

                // Remove slot for the two sheets
                $this->removeSlot($slotId, $sheetSlots[$request->getFromSheet()->getId()]);
                $this->removeSlot($slotId, $sheetSlots[$request->getToSheet()->getId()]);

                $createdMeeting = true;
            }

            if (!$createdMeeting) {
                break;
            }
        }
    }

    /**
     * @param int   $slotId
     * @param array $slots
     */
    private function removeSlot($slotId, &$slots)
    {
        if (($key = array_search($slotId, $slots)) !== false) {
            unset($slots[$key]);
        }
    }
}
