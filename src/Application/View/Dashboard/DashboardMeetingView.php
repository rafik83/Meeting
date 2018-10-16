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

    public function __construct(
        int $allMeetings = 0,
        int $meetingCreatedDayDByAdmin = 0,
        int $meetingCreatedByParticipant = 0,
        int $meetingCreatedByPlanner = 0,
        int $meetingCreatedUpstreamByAdmin = 0,
        int $approvedRequest = 0,
        int $pendingRequest = 0,
        int $refusedRequest = 0
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
    }
}
