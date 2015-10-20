<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class ParticipantView
{
    public $data;

    public $eventId;

    public $eventTitle;

    public $typeTitle;

    public function __construct($data, $eventId, $eventTitle, $typeTitle)
    {
        $this->data       = json_decode($data, true);
        $this->eventId    = $eventId;
        $this->eventTitle = $eventTitle;
        $this->typeTitle  = $typeTitle;
    }
}
