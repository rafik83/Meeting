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

    /** @var ParticipantView[] */
    public $participantViews;

    /**
     * @param int               $id
     * @param string            $title
     * @param ParticipantView[] $participantViews
     */
    public function __construct($id, $title, array $participantViews)
    {
        $this->id               = $id;
        $this->title            = $title;
        $this->participantViews = $participantViews;
    }
}
