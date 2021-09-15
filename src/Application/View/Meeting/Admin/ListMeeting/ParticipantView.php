<?php

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var string|null */
    public $completeName;

    /** @var bool */
    public $checkin;

    /** @var bool */
    public $visio;

    /** @var bool */
    public $present;

    public function __construct(
        int $id,
        ?string $completeName = null,
        bool $checkin = false,
        bool $visio = false,
        bool $present = false
    ) {
        $this->id = $id;
        $this->completeName = $completeName;
        $this->checkin = $checkin;
        $this->visio = $visio;
        $this->present = $present;
    }
}
