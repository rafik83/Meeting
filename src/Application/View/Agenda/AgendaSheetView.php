<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Admin\ParticipantView;

class AgendaSheetView
{
    /**
     * @var AgendaParticipantView[]
     */
    public $participants;

    /**
     * @var array|Admin\ParticipantView[]
     */
    public $requests;

    /**
     * AgendaSheetView constructor.
     *
     * @param AgendaParticipantView[] $participants
     * @param ParticipantView[]       $requests
     */
    public function __construct(array $participants, array $requests)
    {
        $this->participants = $participants;
        $this->requests     = $requests;
    }
}
