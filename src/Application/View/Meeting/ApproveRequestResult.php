<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
