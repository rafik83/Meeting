<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin;

use Proximum\Vimeet\Application\Exception\MeetingRequest\NoSlotAvailableException;
use Proximum\Vimeet\Application\View\Agenda\Admin\RequestSlotView;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class RequestSlotViewQueryHandler
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /**
     * @param SpotRepositoryInterface        $spotRepository
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(
        SpotRepositoryInterface $spotRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository
    ) {
        $this->spotRepository        = $spotRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * @param RequestSlotViewQuery $query
     *
     * @return RequestSlotView
     * @throws NoSlotAvailableException
     */
    public function handle(RequestSlotViewQuery $query)
    {
        $slots = $this->meetingSlotRepository->findAvailableSlotsByParticipantsIds(
            $query->meetingRequest->getEvent(),
            $query->meetingRequest->getParticipantsId(),
            false
        );

        $availableSlotsId = [];

        foreach ($slots as $slot) {
            if (true === $this->spotRepository->hasSpotsForSlotAndParticipantsQuantity(
                $slot,
                $query->meetingRequest->countParticipants(),
                null,
                $query->meetingRequest->getFromSheet(),
                $query->meetingRequest->getToSheet()
            )) {
                $availableSlotsId[] = $slot->getId();
            }
        }

        if (0 === count($availableSlotsId)) {
            throw new NoSlotAvailableException();
        }

        return new RequestSlotView($availableSlotsId);
    }
}
