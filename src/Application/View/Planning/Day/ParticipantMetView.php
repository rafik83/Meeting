<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Planning\Day;

class ParticipantMetView
{
    /** @var null|string */
    public $completeName;

    /** @var null|string */
    public $position;

    public function __construct(
        ?string $completeName = null,
        ?string $position = null
    ) {
        $this->completeName = $completeName;
        $this->position = $position;
    }
}
