<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Planner;

use Proximum\Vimeet\Application\View\Planner\SheetView;
use Proximum\Vimeet\Domain\Model\Event;

class SpotViewQuery
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var SheetView[]
     */
    public $sheets;

    /**
     * @param Event       $event
     * @param SheetView[] $sheets
     */
    public function __construct(Event $event, array $sheets)
    {
        $this->event  = $event;
        $this->sheets = $sheets;
    }
}
