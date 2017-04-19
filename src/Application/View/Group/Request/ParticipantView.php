<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Group\Request;

class ParticipantView
{
    /**
     * @var string
     */
    public $completeName;

    /**
     * @param string $completeName
     */
    public function __construct($completeName)
    {
        $this->completeName = $completeName;
    }
}
