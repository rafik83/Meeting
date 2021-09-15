<?php

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

class ParticipantPresenceView
{
    /** @var int */
    public $id;

    /** @var bool */
    public $present;

    public function __construct(int $id, bool $present = false)
    {
        $this->id = $id;
        $this->present = $present;
    }
}
