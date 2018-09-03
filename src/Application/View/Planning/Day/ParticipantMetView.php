<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
