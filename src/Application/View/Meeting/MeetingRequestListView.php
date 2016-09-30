<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingRequestListView
{
    /**
     * @var MeetingRequestView[]
     */
    private $meetingRequestsView;

    /**
     * @return MeetingRequestView[]
     */
    public function getMeetingRequestsView()
    {
        return $this->meetingRequestsView;
    }

    /**
     * @param MeetingRequestView $meetingRequestView
     */
    public function addRequestView(MeetingRequestView $meetingRequestView)
    {
        $this->meetingRequestsView[] = $meetingRequestView;
    }
}
