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
    public $id;

    public $data;

    public $userEmail;

    public $eventId;

    public $eventTitle;

    public $typeId;

    public $typeTitle;

    public function __construct($id, $data, $userEmail, $eventId, $eventTitle, $typeId, $typeTitle)
    {
        $data = json_decode($data, true);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid json data');
        }

        $this->id         = $id;
        $this->data       = $data;
        $this->userEmail  = $userEmail;
        $this->eventId    = $eventId;
        $this->eventTitle = $eventTitle;
        $this->typeId     = $typeId;
        $this->typeTitle  = $typeTitle;
    }
}
