<?php

namespace Proximum\Vimeet\Application\Query\Meeting\Admin\ListMeeting;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class MeetingListViewQuery implements Query
{
    /** @var Event */
    public $event;

    /** @var MeetingSlot|null */
    public $slot;

    /** @var string */
    public $locale;

    public function __construct(
        Event $event,
        string $locale,
        MeetingSlot $currentSlot = null
    ) {
        $this->event = $event;
        $this->slot = $currentSlot;
        $this->locale = $locale;
    }
}
