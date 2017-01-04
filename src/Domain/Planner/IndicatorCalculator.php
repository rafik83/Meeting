<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Planner;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UnavailabilityRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\MeetingSlotRepository;

class IndicatorCalculator
{
    /**
     * @var UnavailabilityRepositoryInterface
     */
    private $unavailabilityRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MeetingSlotRepository
     */
    private $slotRepository;

    /**
     * @var null|MeetingSlot[]
     */
    private $slots = null;

    /**
     * @var PlanningQuantityGuesser
     */
    private $planningQuantityGuesser;

    /**
     * @param UnavailabilityRepositoryInterface $unavailabilityRepository
     * @param RequestRepositoryInterface        $requestRepository
     * @param MeetingSlotRepository             $slotRepository
     * @param PlanningQuantityGuesser           $planningQuantityGuesser
     */
    public function __construct(
        UnavailabilityRepositoryInterface $unavailabilityRepository,
        RequestRepositoryInterface $requestRepository,
        MeetingSlotRepository $slotRepository,
        PlanningQuantityGuesser $planningQuantityGuesser
    ) {
        $this->unavailabilityRepository = $unavailabilityRepository;
        $this->requestRepository        = $requestRepository;
        $this->slotRepository           = $slotRepository;
        $this->planningQuantityGuesser = $planningQuantityGuesser;
    }

    /**
     * Avoid calling the db for the number of slots by preloading it
     *
     * @param MeetingSlot[] $slots
     */
    public function preloadSlot(array $slots)
    {
        $this->slots = $slots;
    }

    public function getIndicator(Sheet $sheet)
    {
        if (null === $this->slots) {
            $this->slots = $this->slotRepository->countByEvent($sheet->getEvent());
        }

        $participantsCount    = $sheet->countParticipant();
        $meetingRequestsCount = $this->requestRepository->countSheetState($sheet, ['state' => Request::STATE_APPROVED]);
        $planningQuantity     = $this->planningQuantityGuesser->guess($sheet);

        // To check with other service
        $unavailabilitiesCount = 0;

        return new IndicatorView(
            count($this->slots),
            $participantsCount,
            $unavailabilitiesCount,
            $planningQuantity,
            $meetingRequestsCount
        );
    }
}
