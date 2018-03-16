<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\View\Dashboard\DashboardMeetingView;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class DashboardMeetingViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->requestRepository = $requestRepository;
    }

    public function handle(DashboardMeetingViewQuery $query): DashboardMeetingView
    {
        $allMeetings = $this->meetingRepository->countByEvent($query->event);
        $meetingCreatedDayD = 0;

        if ($query->event->hasDay()) {
            $meetingCreatedDayD = $this->meetingRepository->countBetweenDatesByEvent(
                $query->event,
                $query->event->getFirstDay()->getBegin(),
                $query->event->getLastDay()->getEnd()
            );
        }

        $meetingCreatedByParticipant = $this->meetingRepository->countCreatedByParticipantByEvent($query->event);

        $approvedRequest = $this->requestRepository->countApprovedByEvent($query->event);
        $pendingRequest = $this->requestRepository->countPendingByEvent($query->event);
        $refusedRequest = $this->requestRepository->countRefusedByEvent($query->event);

        return new DashboardMeetingView(
            $allMeetings,
            $meetingCreatedDayD,
            $meetingCreatedByParticipant,
            $approvedRequest,
            $pendingRequest,
            $refusedRequest
        );
    }
}
