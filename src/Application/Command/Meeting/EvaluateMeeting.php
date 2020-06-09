<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class EvaluateMeeting implements Command
{
    /** @var User */
    public $user;

    /** @var Meeting */
    public $meeting;

    /** @var int|null */
    public $evaluation;

    /** @var Sheet */
    public $sheet;

    /** @var Event */
    public $event;

    public function __construct(
        Event $event,
        Sheet $sheet,
        Meeting $meeting,
        User $user
    ) {
        $this->meeting = $meeting;
        $this->sheet = $sheet;
        $this->evaluation = null;
        $this->event = $event;
        $this->user = $user;
    }
}
