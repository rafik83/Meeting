<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\View;

class ParticipantNameView
{
    /**
     * @var string
     */
    public $participantName;

    /**
     * @param string $participantName
     */
    public function __construct($participantName)
    {
        $this->participantName = $participantName;
    }
}
