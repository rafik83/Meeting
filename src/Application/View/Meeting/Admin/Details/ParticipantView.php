<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting\Admin\Details;

class ParticipantView
{
    /**
     * @var string
     */
    public $fullName;

    /**
     * @param string $fullName
     */
    public function __construct($fullName)
    {
        $this->fullName = $fullName;
    }
}
