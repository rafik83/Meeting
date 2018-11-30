<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant\Sheet;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var string */
    public $fullName;

    public function __construct(int $id, string $fullName)
    {
        $this->id = $id;
        $this->fullName = $fullName;
    }
}
