<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet\Group\Participant;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var ParticipantView[] $participants */
    public $participants;

    /**
     * @param int               $id
     * @param string            $title
     * @param ParticipantView[] $participants
     */
    public function __construct($id, $title, array $participants)
    {
        $this->id           = $id;
        $this->title        = $title;
        $this->participants = $participants;
    }
}
