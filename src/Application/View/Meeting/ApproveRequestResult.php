<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class ApproveRequestResult
{
    /**
     * @var MeetingDdayView
     */
    public $meetingView;

    /**
     * @var bool
     */
    public $hasError;

    /**
     * TransformRequestIntoMeetingView constructor.
     *
     * @param null|MeetingDdayView $meetingView
     * @param bool                 $hasError
     */
    public function __construct(MeetingDdayView $meetingView = null, bool $hasError = false)
    {
        $this->meetingView = $meetingView;
        $this->hasError    = $hasError;
    }
}
