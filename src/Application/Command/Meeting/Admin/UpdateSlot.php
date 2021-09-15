<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class UpdateSlot implements Command
{
    /** @var Meeting */
    public $meeting;

    /** @var MeetingSlot */
    public $slot;

    /** @var bool */
    public $visio;

    /** @var bool */
    public $isUpdatedByParticipant;

    public function __construct(
        Meeting $meeting,
        MeetingSlot $slot,
        bool $visio = false,
        bool $isUpdatedByParticipant = false
    ) {
        $this->meeting = $meeting;
        $this->slot = $slot;
        $this->visio = $visio;
        $this->isUpdatedByParticipant = $isUpdatedByParticipant;
    }
}
