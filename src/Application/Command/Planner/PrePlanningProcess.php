<?php

namespace Proximum\Vimeet\Application\Command\Planner;

use Proximum\Vimeet\Domain\Model\Event;

class PrePlanningProcess
{
    /** @var Event */
    public $event;

    /** @var string */
    public $solutionType;

    public function __construct(Event $event, string $solutionType)
    {
        $this->event = $event;
        $this->solutionType = $solutionType;
    }
}
