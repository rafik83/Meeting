<?php

namespace Proximum\Vimeet\Application\Command\MeetingSlot;

use Proximum\Vimeet\Application\Command\Command;

class Move implements Command
{
    /** @var int */
    public $slotId;

    /** @var string */
    public $comment;
}
