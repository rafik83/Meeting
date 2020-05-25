<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class PreviousMeetingEvaluationChecker
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /** @var Meeting */
    public $meeting;

    /** @var string */
    public $origin;

    public function __construct(
        string $origin,
        Event $event,
        Sheet $sheet,
        User $user,
        Meeting $meeting
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->meeting = $meeting;
        $this->origin = $origin;
    }
}
