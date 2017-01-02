<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Agenda\SheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;

class SheetListViewQueryHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * SheetListViewQueryHandler constructor.
     *
     * @param SheetRepositoryInterface          $sheetRepository
     * @param MeetingRepositoryInterface        $meetingRepository
     * @param RequestRepositoryInterface        $requestRepository
     * @param MeetingSlotRepositoryInterface    $meetingSlotRepository
     * @param ParticipantRepositoryInterface    $participantRepository
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param SheetInfoGuesser                  $sheetInfoGuesser
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        ParticipantRepositoryInterface $participantRepository,
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->meetingRepository        = $meetingRepository;
        $this->sheetRepository          = $sheetRepository;
        $this->requestRepository        = $requestRepository;
        $this->meetingSlotRepository    = $meetingSlotRepository;
        $this->participantRepository    = $participantRepository;
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->sheetInfoGuesser         = $sheetInfoGuesser;
    }

    /**
     * @param SheetListViewQuery $sheetListViewQuery
     *
     * @return SheetView[]
     */
    public function handle(SheetListViewQuery $sheetListViewQuery)
    {
        $sheetList = [];

        $sheets = $this->sheetRepository->getEnabledSheetsByEvent($sheetListViewQuery->event);

        /** @var Sheet $sheet */
        foreach ($sheets as $sheet) {
            $requestNumber  = $this->requestRepository->countRequestSentBySheet($sheet);
            $proposalNumber = $this->requestRepository->countPropositionReceivedBySheet($sheet);
            $totalSlots     = $this->getSlotsBySheet($sheetListViewQuery->event, $sheet);
            $sheetList[] = new SheetView(
                $sheet->getId(),
                $this->sheetInfoGuesser->guessSheetTitle($sheet, $sheetListViewQuery->locale),
                $sheet->getType()->getTitle($sheetListViewQuery->locale),
                count($sheet->getParticipants()),
                $requestNumber,
                $proposalNumber,
                $this->requestRepository->countApprovedPropositionReceivedBySheet($sheet),
                $totalSlots,
                $this->getUsableSlots($sheet, $sheetListViewQuery->event, $requestNumber, $totalSlots),
                $this->getPlacedAppointmentsNumber($sheet),
                $sheet->getFollower()
            );
        }

        return $sheetList;
    }

    /**
     * @param Event $event
     *
     * @return int
     */
    private function getSlotsBySheet(Event $event, Sheet $sheet)
    {
        $participantsNumber = $this->participantRepository->countParticipantBySheet($sheet);

        return $this->meetingSlotRepository->countByEvent($event) * $participantsNumber;
    }

    /**
     * @param Sheet $sheet
     * @param Event $event
     * @param int   $meetingRequestsCount
     * @param int   $totalSlots
     *
     * @return mixed
     */
    private function getUsableSlots(Sheet $sheet, Event $event, $meetingRequestsCount, $totalSlots)
    {
        // Total slot taking into account of plannings quantity
        $slotCount = $totalSlots * count($sheet->getPackage()->getPlans());

        $unavailabilitiesCount = $this->getUnavailabilitiesBySheet($sheet);

        $sheetSlots = $this->getSlotsBySheet($event, $sheet);

        // Count slots where participant is available
        $availableSlotsCount = $sheetSlots - $unavailabilitiesCount;

        return min($meetingRequestsCount, $slotCount, $availableSlotsCount);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    private function getUnavailabilitiesBySheet(Sheet $sheet)
    {
        $participants = $sheet->getParticipants();

        $unavailabilitiesCount = 0;

        foreach ($participants as $participant) {
            $unavailabilitiesCount += $this->unavailabilityRepository->countByParticipant($participant);
        }

        return $unavailabilitiesCount;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    private function getPlacedAppointmentsNumber(Sheet $sheet)
    {
        $participants        = $sheet->getParticipants();
        $countPlacedMeetings = 0;

        foreach ($participants as $participant) {
            $countPlacedMeetings += $this->meetingRepository->countByParticipant($participant);
        }

        return $countPlacedMeetings;
    }
}
