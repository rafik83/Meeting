<?php

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class PlannerViewQuery
{
    /** @var Event */
    public $event;

    /** @var string */
    public $locale;

    /** @var string one of SolutionType constants */
    public $exportSolutionType;

    public function __construct(
        Event $event,
        string $locale,
        string $exportSolutionType = ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
    ) {
        $this->event = $event;
        $this->locale = $event->getAvailableLocale($locale);
        $this->exportSolutionType = $exportSolutionType;
    }
}
