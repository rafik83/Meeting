<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

class Update implements Command
{
    public Sheet $sheet;
    public Meeting $meeting;
    /** @var Participant[] */
    public array $participants = [];
    public MeetingSlot $meetingSlot;
    public ?string $content = null;

    public function __construct(Sheet $sheet, Meeting $meeting, array $participants, MeetingSlot $meetingSlot)
    {
        $this->sheet = $sheet;
        $this->meeting = $meeting;
        $this->participants = $participants;
        $this->meetingSlot = $meetingSlot;
    }
}
