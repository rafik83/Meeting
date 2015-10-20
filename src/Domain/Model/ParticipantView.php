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

    public $userEmail;

    public $eventId;

    public $eventTitle;

    public $typeId;

    public $typeTitle;

    public function __construct($data, $userEmail, $eventId, $eventTitle, $typeId, $typeTitle)
    {
        $this->data       = json_decode($data, true);
        $this->userEmail  = $userEmail;
        $this->eventId    = $eventId;
        $this->eventTitle = $eventTitle;
        $this->typeId     = $typeId;
        $this->typeTitle  = $typeTitle;
    }
}
