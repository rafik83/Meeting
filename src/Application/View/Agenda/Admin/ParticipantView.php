<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin;

class ParticipantView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $fullName;

    /**
     * ParticipantView constructor.
     *
     * @param int    $id
     * @param string $fullName
     */
    public function __construct($id, $fullName)
    {
        $this->id       = $id;
        $this->fullName = $fullName;
    }
}
