<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardRequestView;
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

        $dashboardRequestViews = $this->requestRepository->getDashboardRequestViewsByEvent($query->event);

        return new DashboardMeetingView(
            $allMeetings,
            $meetingCreatedDayDByAdmin,
            $meetingCreatedByParticipant,
            $meetingCreatedByPlanner,
            $meetingCreatedUpstreamByAdmin,
            $this->countApprovedRequests($dashboardRequestViews),
            $this->countPendingRequests($dashboardRequestViews),
            $this->countRefusedRequests($dashboardRequestViews),
            $this->requestsByType($dashboardRequestViews)
        );
    }

    /**
     * @param DashboardRequestView[] $dashboardRequestViews
     *
     * @return int
     */
    private function countApprovedRequests(array &$dashboardRequestViews): int
    {
        $approved = 0;

        foreach ($dashboardRequestViews as $dashboardRequestView) {
            if ($dashboardRequestView->isApproved()) {
                ++$approved;
            }
        }

        return $approved;
    }

    /**
     * @param DashboardRequestView[] $dashboardRequestViews
     *
     * @return int
     */
    private function countPendingRequests(array &$dashboardRequestViews): int
    {
        $pending = 0;

        foreach ($dashboardRequestViews as $dashboardRequestView) {
            if ($dashboardRequestView->isPending()) {
                ++$pending;
            }
        }

        return $pending;
    }

    /**
     * @param DashboardRequestView[] $dashboardRequestViews
     *
     * @return int
     */
    private function countRefusedRequests(array &$dashboardRequestViews): int
    {
        $refused = 0;

        foreach ($dashboardRequestViews as $dashboardRequestView) {
            if ($dashboardRequestView->isRefused()) {
                ++$refused;
            }
        }

        return $refused;
    }

    /**
     * @param DashboardRequestView[] $dashboardRequestViews
     *
     * @return array
     */
    private function requestsByType(array &$dashboardRequestViews): array
    {
        $requestsByType = [];

        foreach ($dashboardRequestViews as $dashboardRequestView) {
            $typeId = $dashboardRequestView->getFromTypeId();

            if (!isset($requestsByType[$typeId])) {
                $requestsByType[$typeId] = 1;
            } else {
                ++$requestsByType[$typeId];
            }
        }

        return $requestsByType;
    }
}
