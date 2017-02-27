<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Indicator;

use Proximum\Vimeet\Domain\Model\Event;

class SheetIndicatorsLazyLoadViewQuery
{
    /**
     * @var array
     */
    public $sheets;

    /**
     * @var Event
     */
    public $event;

    /**
     * @param Event $event
     * @param array $sheets array of sheet ids
     */
    public function __construct(Event $event, array $sheets)
    {
        $this->event  = $event;
        $this->sheets = $sheets;
    }
}
