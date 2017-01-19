<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda;

use Proximum\Vimeet\Application\View\Agenda\Admin\RequestView;

class AgendaSheetView
{
    /**
     * @var AgendaParticipantView[]
     */
    public $participants;

    /**
     * @var RequestView[]
     */
    public $requests;

    /**
     * AgendaSheetView constructor.
     *
     * @param AgendaParticipantView[] $participants
     * @param RequestView[]           $requests
     */
    public function __construct(array $participants, array $requests)
    {
        $this->participants = $participants;
        $this->requests     = $requests;
    }
}
