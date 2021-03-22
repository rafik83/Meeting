<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Visio;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class PreviousMeetingEvaluationChecker
{
    /** @var Event */
    public $event;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    public TimeRangeInterface $timeRange;

    /** @var string */
    public $origin;

    public function __construct(
        string $origin,
        Event $event,
        Sheet $sheet,
        User $user,
        TimeRangeInterface $timeRange
    ) {
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->timeRange = $timeRange;
        $this->origin = $origin;
    }
}
