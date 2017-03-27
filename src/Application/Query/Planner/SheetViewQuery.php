<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\TypeView;
use Proximum\Vimeet\Domain\Model\Event;

class SheetViewQuery
{
    /** @var Event */
    public $event;

    /** @var TypeView[] */
    public $types;

    /** @var string */
    public $exportSolutionType;

    /**
     * @param Event      $event
     * @param TypeView[] $types
     * @param string     $exportSolutionType
     */
    public function __construct(Event $event, array $types, $exportSolutionType)
    {
        $this->event              = $event;
        $this->types              = $types;
        $this->exportSolutionType = $exportSolutionType;
    }
}
