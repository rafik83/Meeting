<?php

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

    /** @var bool */
    public $isPriority;

    public function __construct(
        MeetingRequest $meetingRequest,
        Sheet $sheet,
        User $user,
        string $locale,
        bool $isMeetingPublished,
        bool $isMeetingRequestUpdateLocked,
        bool $isMeetingRequestClosed = false,
        bool $isAnsweringMeetingRequestClosed = false,
        bool $isSeenByUser = false,
        bool $isPhoneValidationRequired = false,
        bool $showCategory = false,
        bool $isPriority = false
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
        $this->isPriority = $isPriority;
    }
}
