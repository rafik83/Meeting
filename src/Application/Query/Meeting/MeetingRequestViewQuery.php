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
use Proximum\Vimeet\Domain\Model\User;

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
     * @var User
     */
    public $user;

    /**
     * @var bool
     */
    public $isMeetingPublished;

    /**
     * @var bool
     */
    public $isMeetingRequestUpdateLocked;

    /**
     * MeetingRequestViewQuery constructor.
     *
     * @param MeetingRequest $meetingRequest
     * @param Sheet          $sheet
     * @param string         $locale
     * @param User           $user
     * @param bool           $isMeetingPublished
     * @param bool           $isMeetingRequestUpdateLocked
     */
    public function __construct(
        MeetingRequest $meetingRequest,
        Sheet $sheet,
        $locale,
        User $user,
        $isMeetingPublished,
        $isMeetingRequestUpdateLocked
    ) {
        $this->meetingRequest               = $meetingRequest;
        $this->locale                       = $locale;
        $this->sheet                        = $sheet;
        $this->isMeetingPublished           = $isMeetingPublished;
        $this->isMeetingRequestUpdateLocked = $isMeetingRequestUpdateLocked;
        $this->user                         = $user;
    }
}
