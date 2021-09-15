<?php

namespace Proximum\Vimeet\Application\Command\Planner\PrePlanningProcess;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class PrePlanningProcess implements Command
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
