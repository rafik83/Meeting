<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\MeetingRequest\Export;

class MeetingRequestListView
{
    /** @var MeetingRequestView[] */
    public $meetingRequests;

    /**
     * @param MeetingRequestView[] $meetingRequests
     */
    public function __construct(array $meetingRequests)
    {
        $this->meetingRequests = $meetingRequests;
    }
}
