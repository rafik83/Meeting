<?php

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingListView
{
    /** @var MeetingSlot|null */
    public $slot;

    /** @var int */
    public $totalMeetings;

    /** @var MeetingView[] */
    public $meetingViews;

    public function __construct(
        int $totalMeetings,
        ?MeetingSlot $slot = null
    ) {
        $this->totalMeetings = $totalMeetings;
        $this->slot = $slot;
        $this->meetingViews = [];
    }

    public function getNumberOfMeetingsOnThisSlot(): int
    {
        return \count($this->meetingViews);
    }

    public function addMeetingView(MeetingView $meetingView): void
    {
        $this->meetingViews[$meetingView->id] = $meetingView;
    }
}
