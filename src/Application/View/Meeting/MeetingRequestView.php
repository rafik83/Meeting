<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

use Proximum\Vimeet\Application\View\Sheet\Preview\PreviewView;
use Proximum\Vimeet\Domain\Model\Meeting\Request as MeetingRequest;
use Proximum\Vimeet\Domain\Model\Sheet;

class MeetingRequestView
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $state;

    /** @var string */
    public $type;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var MeetingRequest */
    public $meetingRequest;

    /** @var PreviewView[] */
    public $previewViews;

    /** @var string */
    public $sheetName;

    /** @var bool */
    public $isMeetingPublished;

    /** @var bool */
    public $isMeetingRequestUpdateLocked;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /** @var bool */
    public $hasMessage;

    /** @var bool */
    public $isSeenByCurrentUser;

    /** @var bool */
    public $isPhoneValidationRequired;

    /**
     * @var string
     */
    public $validatePhoneLink;

    /**
     * MeetingRequestView constructor.
     *
     * @param Sheet              $sheet
     * @param string             $sheetName
     * @param string             $state
     * @param string             $type
     * @param \DateTimeInterface $createdAt
     * @param MeetingRequest     $meetingRequest
     * @param PreviewView[]      $previewViews
     * @param bool               $isMeetingPublished
     * @param bool               $isMeetingRequestUpdateLocked
     * @param bool               $isMeetingRequestClosed
     * @param bool               $isAnsweringMeetingRequestClosed
     * @param bool               $hasMessage
     * @param bool               $isSeenByCurrentUser
     * @param bool               $isPhoneValidationRequired
     * @param string|null        $validatePhoneLink
     */
    public function __construct(
        Sheet $sheet,
        $sheetName,
        $state,
        $type,
        \DateTimeInterface $createdAt,
        MeetingRequest $meetingRequest,
        array $previewViews,
        $isMeetingPublished = false,
        $isMeetingRequestUpdateLocked = false,
        $isMeetingRequestClosed = false,
        $isAnsweringMeetingRequestClosed = false,
        $hasMessage = false,
        $isSeenByCurrentUser = false,
        $isPhoneValidationRequired = false,
        $validatePhoneLink = null
    ) {
        $this->sheet                           = $sheet;
        $this->sheetName                       = $sheetName;
        $this->state                           = $state;
        $this->type                            = $type;
        $this->createdAt                       = $createdAt;
        $this->meetingRequest                  = $meetingRequest;
        $this->previewViews                    = $previewViews;
        $this->isMeetingPublished              = $isMeetingPublished;
        $this->isMeetingRequestUpdateLocked    = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
        $this->hasMessage                      = $hasMessage;
        $this->isSeenByCurrentUser             = $isSeenByCurrentUser;
        $this->isPhoneValidationRequired       = $isPhoneValidationRequired;
        $this->validatePhoneLink               = $validatePhoneLink;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        return $this->meetingRequest->isSent();
    }

    /**
     * @return bool
     */
    public function isProposition()
    {
        return $this->meetingRequest->getFromSheet() === $this->sheet;
    }
}
