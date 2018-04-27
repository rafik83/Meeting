<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting\Message;

use Proximum\Vimeet\Domain\Model\Meeting;

class DiscussionMeetingRequestViewQuery
{
    /**
     * @var Meeting\Request
     */
    public $meetingRequest;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param Meeting\Request $meetingRequest
     * @param string          $locale
     */
    public function __construct(Meeting\Request $meetingRequest, $locale)
    {
        $this->meetingRequest = $meetingRequest;
        $this->locale         = $locale;
    }
}
