<?php

namespace Proximum\Vimeet\Application\Query\Agenda;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class MeetingViewQuery
{
    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $locale;

    /** @var Sheet */
    public $currentSheet;

    /** @var Event */
    public $event;

    /** @var bool */
    public $isUserParticipantMultipleSheets;

    /** @var User */
    public $user;

    /**
     * @param Meeting $meeting
     * @param Sheet   $currentSheet
     * @param bool    $isUserParticipantMultipleSheets
     * @param User    $user
     * @param Event   $event
     * @param string  $locale
     */
    public function __construct(
        Meeting $meeting,
        Sheet $currentSheet,
        $isUserParticipantMultipleSheets,
        User $user,
        Event $event,
        $locale
    ) {
        $this->meeting = $meeting;
        $this->currentSheet = $currentSheet;
        $this->isUserParticipantMultipleSheets = $isUserParticipantMultipleSheets;
        $this->user = $user;
        $this->event = $event;
        $this->locale = $locale;
    }
}
