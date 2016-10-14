<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestViewQuery
{
    /**
     * @var MeetingRequest
     */
    public $meetingRequest;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var string
     */
    public $locale;

    /**
     * MeetingRequestViewQuery constructor.
     *
     * @param MeetingRequest $meetingRequest
     * @param Sheet          $sheet
     * @param string         $locale
     */
    public function __construct(MeetingRequest $meetingRequest, Sheet $sheet, $locale)
    {
        $this->meetingRequest = $meetingRequest;
        $this->locale         = $locale;
        $this->sheet          = $sheet;
    }
}
