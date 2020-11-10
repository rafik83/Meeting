<?php

namespace Proximum\Vimeet\Application\Query\Dashboard;

use Proximum\Vimeet\Application\Query\Dashboard\View\DashboardRequestView;
use Proximum\Vimeet\Application\View\Dashboard\DashboardMeetingView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;

class DashboardMeetingViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        RequestRepositoryInterface $requestRepository,
        ChatSessionRepositoryInterface $chatSessionRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->requestRepository = $requestRepository;
        $this->chatSessionRepository = $chatSessionRepository;
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

        $meetingCallVisio = $this->chatSessionRepository->countCallVisioByEvent($query->event);

        $dashboardRequestViews = $this->requestRepository->getDashboardRequestViewsByEvent($query->event);

        return new DashboardMeetingView(
            $allMeetings + $meetingCallVisio,
            $meetingCreatedDayDByAdmin,
            $meetingCreatedByParticipant,
            $meetingCreatedByPlanner,
            $meetingCreatedUpstreamByAdmin,
            $meetingCallVisio,
            $this->countApprovedRequests($dashboardRequestViews),
            $this->countPendingRequests($dashboardRequestViews),
            $this->countRefusedRequests($dashboardRequestViews),
            $this->requestsByType($dashboardRequestViews),
            $this->requestsByTypeAndState($dashboardRequestViews)
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

    /**
     * @param DashboardRequestView[] $dashboardRequestViews
     *
     * @return array
     */
    private function requestsByTypeAndState(array &$dashboardRequestViews): array
    {
        $requestsByTypeAndState = [];

        foreach ($dashboardRequestViews as $dashboardRequestView) {
            $typeId = $dashboardRequestView->getFromTypeId();
            $state = $dashboardRequestView->getState();

            if (!isset($requestsByTypeAndState[$typeId][$state])) {
                $requestsByTypeAndState[$typeId][$state] = 1;
            } else {
                ++$requestsByTypeAndState[$typeId][$state];
            }

            if ($dashboardRequestView->isPlanned()) {
                if (!isset($requestsByTypeAndState[$typeId][Meeting\Request::STATE_PLANNED])) {
                    $requestsByTypeAndState[$typeId][Meeting\Request::STATE_PLANNED] = 1;
                } else {
                    ++$requestsByTypeAndState[$typeId][Meeting\Request::STATE_PLANNED];
                }
            }
        }

        return $requestsByTypeAndState;
    }
}
