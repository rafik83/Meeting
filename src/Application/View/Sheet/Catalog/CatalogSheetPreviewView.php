<?php

namespace Proximum\Vimeet\Application\View\Sheet\Catalog;

use Proximum\Vimeet\Application\View\Participant\CardListView;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;

class CatalogSheetPreviewView extends AbstractSheetPreviewView
{
    /** @var Meeting\Request|null */
    public $meetingRequest;

    /** @var bool */
    public $isItMySheet;

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

    /** @var string|null */
    public $validatePhoneLink;

    /** @var bool */
    public $isPriority;

    private bool $canRequestMeeting;

    public function __construct(
        int $id,
        Sheet $sheet,
        $title,
        $type,
        array $preview,
        ?Meeting\Request $meetingRequest,
        bool $isItMySheet,
        bool $isMeetingPublished,
        bool $isMeetingRequestUpdateLocked,
        bool $isMeetingRequestClosed,
        bool $isAnsweringMeetingRequestClosed,
        bool $hasMessage,
        bool $isSeenByCurrentUser,
        bool $isPhoneValidationRequired,
        ?string $validatePhoneLink,
        bool $isPriority,
        bool $canRequestMeeting
    ) {
        parent::__construct($id, $title, $type, $preview, $sheet);

        $this->meetingRequest                  = $meetingRequest;
        $this->isItMySheet                     = $isItMySheet;
        $this->isMeetingPublished              = $isMeetingPublished;
        $this->isMeetingRequestUpdateLocked    = $isMeetingRequestUpdateLocked;
        $this->isMeetingRequestClosed          = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
        $this->hasMessage                      = $hasMessage;
        $this->isSeenByCurrentUser             = $isSeenByCurrentUser;
        $this->isPhoneValidationRequired       = $isPhoneValidationRequired;
        $this->validatePhoneLink               = $validatePhoneLink;
        $this->isPriority = $isPriority;
        $this->canRequestMeeting = $canRequestMeeting;
    }

    /**
     * @return bool
     */
    public function hasMeetingRequest()
    {
        return null !== $this->meetingRequest;
    }

    /**
     * @return bool
     */
    public function isAllowedToCreateMeetingRequest()
    {
        return null === $this->meetingRequest && $this->canRequestMeeting;
    }

    /**
     * @return bool
     */
    public function meetingRequestIsPending()
    {
        return $this->meetingRequest->isSent();
    }

    /**
     * @return bool
     */
    public function meetingRequestIsProposition()
    {
        return $this->meetingRequest->getFromSheet() === $this->sheet;
    }
}
