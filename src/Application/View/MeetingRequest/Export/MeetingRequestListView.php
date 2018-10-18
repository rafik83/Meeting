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

    /** @var string */
    public $timeZone;

    /** @var string */
    public $locale;

    public function __construct(
        array $meetingRequests,
        string $timeZone,
        string $locale
    ) {
        $this->meetingRequests = $meetingRequests;
        $this->timeZone = $timeZone;
        $this->locale = $locale;
    }
}
