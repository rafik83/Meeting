<?php

namespace Proximum\Vimeet\Application\View\Dashboard;

class DashboardMeetingView
{
    /** @var int */
    public $meeting;

    /** @var int */
    public $meetingCreatedUpstreamByAdmin;

    /** @var int */
    public $liveMeeting;

    /** @var int */
    public $approvedRequest;

    /** @var int */
    public $pendingRequest;

    /** @var int */
    public $refusedRequest;

    /** @var int */
    public $request;

    /** @var int */
    public $meetingCreatedByParticipant;

    /** @var int */
    public $meetingCreatedDayDByAdmin;

    /** @var int */
    public $meetingCreatedByPlanner;

    /** @var int */
    public $meetingCallVisio;

    /** @var array */
    public $requestsByType;

    /** @var array */
    public $requestsByTypeAndState;

    public function __construct(
        int $allMeetings,
        int $meetingCreatedDayDByAdmin,
        int $meetingCreatedByParticipant,
        int $meetingCreatedByPlanner,
        int $meetingCreatedUpstreamByAdmin,
        int $meetingCallVisio,
        int $approvedRequest,
        int $pendingRequest,
        int $refusedRequest,
        array $requestsByType,
        array $requestsByTypeAndState
    ) {
        $this->meeting = $allMeetings;
        $this->meetingCreatedDayDByAdmin = $meetingCreatedDayDByAdmin;
        $this->meetingCreatedByParticipant = $meetingCreatedByParticipant;
        $this->meetingCreatedByPlanner = $meetingCreatedByPlanner;
        $this->meetingCreatedUpstreamByAdmin = $meetingCreatedUpstreamByAdmin;
        $this->meetingCallVisio = $meetingCallVisio;

        $this->approvedRequest = $approvedRequest;
        $this->pendingRequest = $pendingRequest;
        $this->refusedRequest = $refusedRequest;
        $this->request = $approvedRequest + $pendingRequest + $refusedRequest;

        $this->requestsByType = $requestsByType;
        $this->requestsByTypeAndState = $requestsByTypeAndState;
    }
}
