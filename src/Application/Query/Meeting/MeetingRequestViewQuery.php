<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class MeetingRequestViewQuery
{
    /** @var MeetingRequest */
    public $meetingRequest;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var string */
    public $locale;

    /** @var bool */
    public $isMeetingPublished;

    /** @var bool */
    public $isMeetingRequestUpdateLocked;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /** @var bool */
    public $isSeenByUser;

    /** @var bool */
    public $isPhoneValidationRequired;

    /** @var bool */
    public $showCategory;

    /**
     * @param MeetingRequest $meetingRequest
     * @param Sheet          $sheet
     * @param User           $user
     * @param string         $locale
     * @param bool           $isMeetingPublished
     * @param bool           $isMeetingRequestUpdateLocked
     * @param bool           $isMeetingRequestClosed
     * @param bool           $isAnsweringMeetingRequestClosed
     * @param bool           $isSeenByUser
     * @param bool           $isPhoneValidationRequired
     * @param bool           $showCategory
     */
    public function __construct(
        MeetingRequest $meetingRequest,
        Sheet $sheet,
        User $user,
        $locale,
        $isMeetingPublished,
        $isMeetingRequestUpdateLocked,
        $isMeetingRequestClosed = false,
        $isAnsweringMeetingRequestClosed = false,
        $isSeenByUser = false,
        $isPhoneValidationRequired = false,
        bool $showCategory = false
    ) {
        $this->meetingRequest = $meetingRequest;
        $this->locale = $locale;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->isMeetingPublished = $isMeetingPublished;
        $this->isMeetingRequestUpdateLocked = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
        $this->isSeenByUser = $isSeenByUser;
        $this->isPhoneValidationRequired = $isPhoneValidationRequired;
        $this->showCategory = $showCategory;
    }
}
