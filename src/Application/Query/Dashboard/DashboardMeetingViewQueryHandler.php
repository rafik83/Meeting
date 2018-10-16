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
use Proximum\Vimeet\Domain\Model\Meeting;
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
        $meetingCreatedDayDByParticipant = 0;
        $meetingCreatedDayDByAdmin = 0;
        $meetingCreatedUpstreamByAdmin = 0;

        if ($query->event->hasDay()) {
            $firstDay = $query->event->getFirstDay()->getBegin();
            $lastDay = $query->event->getLastDay()->getEnd();

            $meetingCreatedDayDByAdmin = $this->meetingRepository->countBetweenDatesByEventAndType(
                $query->event,
                $firstDay,
                $lastDay,
                Meeting::CREATED_BY_ADMIN
            );

            $meetingCreatedDayDByParticipant = $this->meetingRepository->countBetweenDatesByEventAndType(
                $query->event,
                $firstDay,
                $lastDay,
                Meeting::CREATED_BY_PARTICIPANT
            );

            $meetingCreatedUpstreamByAdmin = $this->meetingRepository->countUpstreamByEventAndType(
                $query->event,
                $firstDay,
                Meeting::CREATED_BY_ADMIN
            );
        }

        $meetingCreatedByParticipant = $this->meetingRepository
            ->countCreatedByEventAndType($query->event, Meeting::CREATED_BY_PARTICIPANT);

        $meetingCreatedByPlanner = $this->meetingRepository
            ->countCreatedByEventAndType($query->event, Meeting::CREATED_BY_PLANNER);

        $approvedRequest = $this->requestRepository->countApprovedByEvent($query->event);
        $pendingRequest = $this->requestRepository->countPendingByEvent($query->event);
        $refusedRequest = $this->requestRepository->countRefusedByEvent($query->event);

        return new DashboardMeetingView(
            $allMeetings,
            $meetingCreatedDayDByAdmin,
            $meetingCreatedDayDByParticipant,
            $meetingCreatedByParticipant,
            $meetingCreatedByPlanner,
            $meetingCreatedUpstreamByAdmin,
            $approvedRequest,
            $pendingRequest,
            $refusedRequest
        );
    }
}
