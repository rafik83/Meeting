<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;

class PreviousHappeningEvaluationChecker
{
    public Event $event;
    public Sheet $sheet;
    public User $user;
    public TimeRangeInterface $timeRange;
    public string $origin;

    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        TimeRangeInterface $timeRange,
        string $origin
    ){
        $this->event = $event;
        $this->sheet = $sheet;
        $this->user = $user;
        $this->timeRange = $timeRange;
        $this->origin = $origin;
    }
}
