<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class PlannerViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var string one of SolutionType constants
     */
    public $exportSolutionType;

    /**
     * @param Event  $event
     * @param string $locale
     * @param string $exportSolutionType
     */
    public function __construct(
        Event $event,
        $locale,
        $exportSolutionType = ExportSolutionType::SOLUTION_OPTIMIZE_MOVING_ALLOWED
    ) {
        $this->event              = $event;
        $this->locale             = $event->getAvailableLocale($locale);
        $this->exportSolutionType = $exportSolutionType;
    }
}
