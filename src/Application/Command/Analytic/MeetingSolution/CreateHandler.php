<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Analytic\MeetingSolution;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Sheet\SheetSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\Spot\SpotSatisfactionListQueryHandler;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQuery;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\SpotFillingRate\SpotFillingRateQueryHandler;
use Proximum\Vimeet\Domain\Model\Analytic\MeetingSolution;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\Analytic\MeetingSolutionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class CreateHandler
{
    /** @var SerializerAdapterInterface */
    private $serializer;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var array */
    private $spotUnavailabilities = [];

    /** @var SheetSatisfactionListQueryHandler */
    private $sheetSatisfactionListQueryHandler;

    /** @var SpotSatisfactionListQueryHandler */
    private $spotSatisfactionListQueryHandler;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var MeetingSolutionRepositoryInterface */
    private $meetingSolutionRepository;

    /** @var SpotFillingRateQueryHandler */
    private $spotFillingRateQueryHandler;

    /**
     * @param SerializerAdapterInterface         $serializer
     * @param MeetingSolutionRepositoryInterface $meetingSolutionRepository
     * @param MeetingRepositoryInterface         $meetingRepository
     * @param RequestRepositoryInterface         $requestRepository
     * @param SpotRepositoryInterface            $spotRepository
     * @param MeetingSlotRepositoryInterface     $slotRepository
     * @param SheetSatisfactionListQueryHandler  $sheetSatisfactionListQueryHandler
     * @param SpotSatisfactionListQueryHandler   $spotSatisfactionListQueryHandler
     * @param SpotFillingRateQueryHandler        $spotFillingRateQueryHandler
     * @param \DateTimeInterface                 $dateTime
     */
    public function __construct(
        SerializerAdapterInterface $serializer,
        MeetingSolutionRepositoryInterface $meetingSolutionRepository,
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $slotRepository,
        SheetSatisfactionListQueryHandler $sheetSatisfactionListQueryHandler,
        SpotSatisfactionListQueryHandler $spotSatisfactionListQueryHandler,
        SpotFillingRateQueryHandler $spotFillingRateQueryHandler,
        \DateTimeInterface $dateTime
    ) {
        $this->serializer = $serializer;
        $this->meetingRepository = $meetingRepository;
        $this->requestRepository = $requestRepository;
        $this->spotRepository = $spotRepository;
        $this->slotRepository = $slotRepository;
        $this->sheetSatisfactionListQueryHandler = $sheetSatisfactionListQueryHandler;
        $this->spotSatisfactionListQueryHandler = $spotSatisfactionListQueryHandler;
        $this->dateTime = $dateTime;
        $this->meetingSolutionRepository = $meetingSolutionRepository;
        $this->spotFillingRateQueryHandler = $spotFillingRateQueryHandler;
    }

    /**
     * @param Create $command
     */
    public function handle(Create $command)
    {
        $countMeeting = $this->meetingRepository->countByEvent($command->event);
        $countRequest = $this->requestRepository->countApprovedByEvent($command->event);

        $sharedSpots = $this->spotRepository->findSharedByEvent($command->event);
        $slots = $this->slotRepository->getAvailableSlotByEvent($command->event);

        $capacity = 0;

        foreach ($slots as $slot) {
            foreach ($sharedSpots as $spot) {
                $capacity += $this->getSpotCapacityForSlot($spot, $slot);
            }
        }

        // Avoid divide by 0
        if (0 === $capacity) {
            $capacity = 1;
        }

        $numberOfMeetingOnShared = $this->meetingRepository->countMeetingForSpots($command->event, $sharedSpots);
        $fillingRate = 100 * ($numberOfMeetingOnShared / $capacity);

        $sheetSatisfaction = $this->serializer->serialize(
            $this->sheetSatisfactionListQueryHandler->handle(new SheetSatisfactionListQuery($command->event)),
            'json'
        );

        $spotSatisfaction = $this->serializer->serialize(
            $this->spotSatisfactionListQueryHandler->handle(new SpotSatisfactionListQuery($command->event, $slots)),
            'json'
        );

        $fillingRateGraph = $this->serializer->serialize(
            $this->spotFillingRateQueryHandler->handle(new SpotFillingRateQuery($command->event, $slots, $sharedSpots)),
            'json'
        );

        $meetingSolution = new MeetingSolution(
            $command->event,
            $countMeeting,
            $countRequest,
            $fillingRate,
            $sheetSatisfaction,
            $spotSatisfaction,
            $fillingRateGraph,
            $this->dateTime
        );

        $this->meetingSolutionRepository->add($meetingSolution);
    }

    /**
     * @param Spot        $spot
     * @param MeetingSlot $slot
     *
     * @return int
     */
    private function getSpotCapacityForSlot(Spot $spot, MeetingSlot $slot): int
    {
        if (!isset($this->spotUnavailabilities[$spot->getId()])) {
            $this->spotUnavailabilities[$spot->getId()] = [];

            foreach ($spot->getSpotUnavailabilities() as $unavailability) {
                $this->spotUnavailabilities[$spot->getId()][$unavailability->getSlot()->getId()] = true;
            }
        }

        if (isset($this->spotUnavailabilities[$spot->getId()][$slot->getId()])) {
            return 0;
        }

        return $spot->getMeetingCapacity();
    }
}
