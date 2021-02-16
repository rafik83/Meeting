<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetPreviewViewQuery
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $viewer;

    /** @var Event */
    public $event;

    /** @var bool */
    public $isMeetingRequestClosed;

    /** @var bool */
    public $isAnsweringMeetingRequestClosed;

    /** @var bool */
    public $isSeenByCurrentUser;

    /** @var bool */
    public $isMobileValidationRequired;

    /** @var User */
    public $user;

    /** @var bool */
    public $showCategory;

    /** @var bool */
    public $isPriority;

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $locale
     * @param Sheet  $viewer
     * @param User   $user
     * @param bool   $isMeetingRequestClosed
     * @param bool   $isAnsweringMeetingRequestClosed
     * @param bool   $isSeenByCurrentUser
     * @param bool   $isMobileValidationRequired
     * @param bool   $showCategory
     * @param bool   $isPriority
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        $locale,
        Sheet $viewer,
        User $user,
        bool $isMeetingRequestClosed = false,
        bool $isAnsweringMeetingRequestClosed = false,
        bool $isSeenByCurrentUser = false,
        bool $isMobileValidationRequired = false,
        bool $showCategory = false,
        bool $isPriority = false
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->locale  = $locale;
        $this->viewer = $viewer;
        $this->isMeetingRequestClosed = $isMeetingRequestClosed;
        $this->isAnsweringMeetingRequestClosed = $isAnsweringMeetingRequestClosed;
        $this->isSeenByCurrentUser = $isSeenByCurrentUser;
        $this->isMobileValidationRequired = $isMobileValidationRequired;
        $this->user = $user;
        $this->showCategory = $showCategory;
        $this->isPriority = $isPriority;
    }
}
