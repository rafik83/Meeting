<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Agenda\Admin\Request;

class RequestParticipantView
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
     * @var bool
     */
    public $participate;

    /**
     * RequestParticipantView constructor.
     *
     * @param int    $id
     * @param string $fullName
     * @param bool   $participate
     */
    public function __construct($id, $fullName, $participate)
    {
        $this->id          = $id;
        $this->fullName    = $fullName;
        $this->participate = $participate;
    }
}
