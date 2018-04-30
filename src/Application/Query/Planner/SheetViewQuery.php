<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Planner\ExportSolutionType;

class SheetViewQuery
{
    /** @var Event */
    public $event;

    /** @var TypeView[] */
    public $types;

    /** @var string one of SolutionType constants */
    public $exportSolutionType;

    /**
     * @param Event      $event
     * @param TypeView[] $types
     * @param string     $exportSolutionType one of SolutionType constants
     */
    public function __construct(Event $event, array $types, $exportSolutionType)
    {
        $this->event              = $event;
        $this->types              = $types;
        $this->exportSolutionType = $exportSolutionType;
    }

    /**
     * @return bool
     */
    public function isSolutionFromScratch()
    {
        return ExportSolutionType::SOLUTION_FROM_SCRATCH === $this->exportSolutionType;
    }
}
