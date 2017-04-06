<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaParticipantView
{
    /**
     * @var
     */
    public $days;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $fullname;

    /**
     * AgendaParticipantView constructor.
     *
     * @param int             $id
     * @param string          $fullname
     * @param AgendaDayView[] $days
     */
    public function __construct($id, $fullname, array $days)
    {
        $this->days     = $days;
        $this->id       = $id;
        $this->fullname = $fullname;
    }
}
