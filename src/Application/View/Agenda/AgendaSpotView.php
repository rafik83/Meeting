<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaSpotView
{
    /**
     * @var DayView[]
     */
    public $days;

    /**
     * @var int "Spot ID"
     */
    public $id;

    /**
     * @var string
     */
    public $reference;

    /**
     * AgendaSpotView constructor.
     *
     * @param int       $id
     * @param string    $reference
     * @param DayView[] $days
     */
    public function __construct($id, $reference, array $days = [])
    {
        $this->days      = $days;
        $this->reference = $reference;
        $this->id        = $id;
    }
}
