<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Application\Adapter\EntityManagerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Exception\Planner\InvalidXmlException;
use Proximum\Vimeet\Application\View\Planner\Result\MeetingResult;
use Proximum\Vimeet\Application\View\Planner\Result\PlannerResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\ParticipantFinder;

class ImportHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SerializerAdapterInterface
     */
    private $serializer;

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
    private $slotRepository;

    /**
     * @var SpotRepositoryInterface
     */
    private $spotRepository;

    /**
     * @var EntityManagerAdapterInterface
     */
    private $entityManagerAdapter;

    /**
     * @var Request[]
     */
    private $requests = [];

    /**
     * @var Sheet[]
     */
    private $sheets = [];

    /**
     * @var MeetingSlot[]
     */
    private $slots = [];

    /**
     * @var Spot[]
     */
    private $spots = [];

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @param SerializerAdapterInterface     $serializer
     * @param MeetingRepositoryInterface     $meetingRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param RequestRepositoryInterface     $requestRepository
     * @param MeetingSlotRepositoryInterface $slotRepository
     * @param SpotRepositoryInterface        $spotRepository
     * @param EntityManagerAdapterInterface  $entityManagerAdapter
     * @param \DateTimeInterface             $dateTime
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        MeetingRepositoryInterface $meetingRepository,
        SheetRepositoryInterface $sheetRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        SpotRepositoryInterface $spotRepository,
        EntityManagerAdapterInterface $entityManagerAdapter,
        \DateTimeInterface $dateTime
    ) {
        $this->serializer           = $serializer;
        $this->meetingRepository    = $meetingRepository;
        $this->sheetRepository      = $sheetRepository;
        $this->requestRepository    = $requestRepository;
        $this->slotRepository       = $slotRepository;
        $this->spotRepository       = $spotRepository;
        $this->entityManagerAdapter = $entityManagerAdapter;
        $this->dateTime             = $dateTime;
    }

    /**
     * @param Import $import
     *
     * @throws InvalidXmlException
     */
    public function handle(Import $import)
    {
        // Remove all meeting of the event
        $this->meetingRepository->deleteAll($import->event);

        $content = file_get_contents($import->file);

        try {
            /** @var PlannerResult $plannerResult */
            $plannerResult = $this->serializer->deserialize($content, PlannerResult::class, 'xml');
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidXmlException();
        }

        $this->handlePlannerResult($import, $plannerResult);
    }

    /**
     * @param Import        $import
     * @param PlannerResult $plannerResult
     */
    private function handlePlannerResult(Import $import, PlannerResult $plannerResult)
    {
        $event = $import->event;
        $this->prepareRequestSheetSlotAndSpot($event);
        $toFlush = [];

        $index = 1;
        foreach ($plannerResult->meetings as $meetingResult) {
            $meeting = $this->handleMeeting($meetingResult);

            if (null !== $meeting) {
                $toFlush[] = $meeting;
            }

            // Each 20 meetings, flush and clear to optimize the insertion
            if (0 === ($index % 20)) {
                $this->entityManagerAdapter->flush();

                /** @var Meeting $flush */
                foreach ($toFlush as $flush) {
                    $this->entityManagerAdapter->detach($flush);
                }

                $toFlush = [];
            }

            $index++;
        }

        $this->entityManagerAdapter->flush();
        $this->entityManagerAdapter->clear();
    }

    /**
     * @param Event $event
     */
    private function prepareRequestSheetSlotAndSpot(Event $event)
    {
        $requests = $this->requestRepository->getAllAcceptedByEvent($event);
        $sheets   = $this->sheetRepository->getByEvent($event);
        $slots    = $this->slotRepository->findByEvent($event);
        $spots    = $this->spotRepository->getActiveByEvent($event);

        foreach ($requests as $request) {
            $this->requests[$request->getId()] = $request;
        }

        foreach ($sheets as $sheet) {
            $this->sheets[$sheet->getId()] = $sheet;
        }

         foreach ($slots as $slot) {
             $this->slots[$slot->getId()] = $slot;
         }

        foreach ($spots as $spot) {
            $this->spots[$spot->getId()] = $spot;
        }
    }

    /**
     * @param MeetingResult $meetingResult
     *
     * @return Meeting|null
     */
    private function handleMeeting(MeetingResult $meetingResult)
    {
        if (!isset($this->sheets[$meetingResult->sheetFrom->id])
            || !isset($this->sheets[$meetingResult->sheetTo->id])
            || !isset($this->requests[$meetingResult->requestId])
            || null === $meetingResult->spot
            || !isset($this->spots[$meetingResult->spot->id])
            || null === $meetingResult->slot
            || !isset($this->slots[$meetingResult->slot->id])
        ) {
            return null; // Early return if element to create the meeting are not present in the db
        }

        $request   = $this->requests[$meetingResult->requestId];
        $sheetFrom = $this->sheets[$meetingResult->sheetFrom->id];
        $sheetTo   = $this->sheets[$meetingResult->sheetTo->id];
        $spot      = $this->spots[$meetingResult->spot->id];
        $slot      = $this->slots[$meetingResult->slot->id];
        $participantsFrom = [];
        $participantsTo   = [];


        // Check if participant are present also
        foreach ($meetingResult->participants as $participant) {
            $participantFrom = ParticipantFinder::getParticipantWithId($sheetFrom, $participant->id);
            $participantTo   = ParticipantFinder::getParticipantWithId($sheetTo, $participant->id);

            if (null !== $participantFrom && $request->hasFromParticipant($participantFrom)) {
                $participantsFrom[] = $participantFrom;
            }

            if (null !== $participantTo && $request->hasToParticipant($participantTo)) {
                $participantsTo[] = $participantTo;
            }

            if ((null === $participantFrom && $participantTo === null)) {
                return null; // Early return if participant of the meeting not found
            }
        }

        $meeting = new Meeting(
            $request,
            $slot,
            $sheetFrom,
            $participantsFrom,
            $sheetTo,
            $participantsTo,
            $this->dateTime,
            $spot
        );

        $this->entityManagerAdapter->persist($meeting);

        return $meeting;
    }
}
