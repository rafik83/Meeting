<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Exception\Meeting\MeetingRequestCanNotBeMeetingException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSpotAvailableException;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestSlotView;
use Proximum\Vimeet\Domain\Meeting\RequestCanBeMeetingChecker;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class RequestSlotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var RequestCanBeMeetingChecker */
    private $requestCanBeMeetingChecker;

    /**
     * @param SpotRepositoryInterface        $spotRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     * @param RequestCanBeMeetingChecker     $requestCanBeMeetingChecker
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        RequestCanBeMeetingChecker $requestCanBeMeetingChecker
    ) {
        $this->spotRepository        = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->requestCanBeMeetingChecker = $requestCanBeMeetingChecker;
    }

    /**
     * @param RequestSlotViewQuery $query
     *
     * @return RequestSlotView
     *
     * @throws MeetingRequestCanNotBeMeetingException
     * @throws NoSlotAvailableException
     * @throws NoSpotAvailableException
     */
    public function handle(RequestSlotViewQuery $query)
    {
        if (false === $this->requestCanBeMeetingChecker->handle($query->meetingRequest)) {
            throw new MeetingRequestCanNotBeMeetingException();
        }

        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipantsIds(
            $query->meetingRequest->getEvent(),
            $query->meetingRequest->getParticipantsId(),
            false
        );

        if (0 === count($slots)) {
            throw new NoSlotAvailableException();
        }

        $availableSlotsId = [];

        foreach ($slots as $slot) {
            if (true === $this->spotRepository->hasSpotsForSlotAndParticipantsQuantity(
                $slot,
                $query->meetingRequest->countParticipants(),
                null,
                $query->meetingRequest->getFromSheet(),
                $query->meetingRequest->getToSheet(),
                $query->visio
            )) {
                $availableSlotsId[] = $slot->getId();
            }
        }

        if (0 === count($availableSlotsId)) {
            throw new NoSpotAvailableException();
        }

        return new RequestSlotView($availableSlotsId);
    }
}
