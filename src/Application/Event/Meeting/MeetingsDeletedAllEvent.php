<?php

namespace Proximum\Vimeet\Application\Event\Meeting;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\EventDispatcher;

class MeetingsDeletedAllEvent extends EventDispatcher\Event
{
    /** @var Event */
    private $event;

    /** @var Admin */
    private $admin;

    public function __construct(Event $event, Admin $admin)
    {
        $this->event = $event;
        $this->admin = $admin;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }
}
