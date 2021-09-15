<?php

namespace Proximum\Vimeet\Application\Query\Planning\Day;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\User;

class MeetingViewQuery
{
    /** @var Meeting */
    public $meeting;

    /**
     * @var User */
    public $user;

    /** @var string */
    public $defaultLocale;

    /** @var Event */
    public $event;

    /**
     * @param Event   $event
     * @param Meeting $meeting
     * @param User    $user
     * @param string  $defaultLocale
     */
    public function __construct(Event $event, Meeting $meeting, User $user, string $defaultLocale)
    {
        $this->meeting = $meeting;
        $this->user = $user;
        $this->defaultLocale = $defaultLocale;
        $this->event = $event;
    }
}
