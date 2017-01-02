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
     * AgendaParticipantView constructor.
     *
     * @param AgendaDayView[] $days
     */
    public function __construct(array $days)
    {
        $this->days = $days;
    }
}
