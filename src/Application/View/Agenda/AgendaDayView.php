<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Slot\AbstractSlotView;
use Proximum\Vimeet\Domain\Model\Event\Day;

class AgendaDayView
{
    /**
     * @var AbstractSlotView[]
     */
    public $slotViews;

    /**
     * @var Day
     */
    public $day;

    /**
     * AgendaDayView constructor.
     *
     * @param Day                $day
     * @param AbstractSlotView[] $slotViews
     */
    public function __construct(Day $day, array $slotViews)
    {
        $this->slotViews = $slotViews;
        $this->day       = $day;
    }
}
