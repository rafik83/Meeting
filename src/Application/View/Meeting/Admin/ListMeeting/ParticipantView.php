<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\ListMeeting;

class ParticipantView
{
    /** @var int */
    public $id;

    /** @var string|null */
    public $completeName;

    public function __construct(
        int $id,
        ?string $completeName = null
    ) {
        $this->id = $id;
        $this->completeName = $completeName;
    }
}
