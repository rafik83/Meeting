<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Domain\Model\Sheet;

class AgendaView
{
    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @param array $dayViews
     * @param Sheet $sheet
     */
    public function __construct(array $dayViews, Sheet $sheet)
    {
        $this->days  = $dayViews;
        $this->sheet = $sheet;
    }

    /**
     * @return int
     */
    public function getNumberOfDays()
    {
        return count($this->days);
    }

    /**
     * In case of one day, take the fullscreen size
     * If more, display 2 column size by size
     *
     * @return int
     */
    public function getColSize()
    {
        return $this->getNumberOfDays() === 1 ? 12 : 6;
    }

}
