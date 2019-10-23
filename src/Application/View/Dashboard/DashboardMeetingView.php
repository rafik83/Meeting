<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /** @var array */
    public $requestsByType;

    public function __construct(
        int $allMeetings,
        int $meetingCreatedDayDByAdmin,
        int $meetingCreatedByParticipant,
        int $meetingCreatedByPlanner,
        int $meetingCreatedUpstreamByAdmin,
        int $approvedRequest,
        int $pendingRequest,
        int $refusedRequest,
        array $requestsByType
    ) {
        $this->meeting = $allMeetings;
        $this->meetingCreatedDayDByAdmin = $meetingCreatedDayDByAdmin;
        $this->meetingCreatedByParticipant = $meetingCreatedByParticipant;
        $this->meetingCreatedByPlanner = $meetingCreatedByPlanner;
        $this->meetingCreatedUpstreamByAdmin = $meetingCreatedUpstreamByAdmin;

        $this->approvedRequest = $approvedRequest;
        $this->pendingRequest = $pendingRequest;
        $this->refusedRequest = $refusedRequest;
        $this->request = $approvedRequest + $pendingRequest + $refusedRequest;

        $this->requestsByType = $requestsByType;
    }
}
