<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

class AgendaSheetView
{
    /**
     * @var AgendaParticipantView[]
     */
    public $participants;

    /**
     * AgendaSheetView constructor.
     *
     * @param AgendaParticipantView[] $participants
     */
    public function __construct(array $participants)
    {
        $this->participants = $participants;
    }
}
