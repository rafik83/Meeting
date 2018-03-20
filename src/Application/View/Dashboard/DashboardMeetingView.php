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
    public $plannedMeeting;

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
    public $meetingCreatedDayD;

    /**
     * @param int $allMeetings
     * @param int $meetingCreatedDayD
     * @param int $meetingCreatedByParticipant
     * @param int $approvedRequest
     * @param int $pendingRequest
     * @param int $refusedRequest
     */
    public function __construct(
        int $allMeetings = 0,
        int $meetingCreatedDayD = 0,
        int $meetingCreatedByParticipant = 0,
        int $approvedRequest = 0,
        int $pendingRequest = 0,
        int $refusedRequest = 0
    ) {
        $this->meeting = $allMeetings;
        $this->meetingCreatedDayD = $meetingCreatedDayD;
        $this->meetingCreatedByParticipant = $meetingCreatedByParticipant;
        $this->plannedMeeting = $allMeetings - $meetingCreatedDayD - $meetingCreatedByParticipant;

        $this->approvedRequest = $approvedRequest;
        $this->pendingRequest = $pendingRequest;
        $this->refusedRequest = $refusedRequest;
        $this->request = $approvedRequest + $pendingRequest + $refusedRequest;
    }
}
