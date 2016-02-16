<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Preview;

class ParticipantDataView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $email;

    /**
     * @var array
     */
    public $rows;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var bool
     */
    public $editable;

    /**
     * ParticpantView constructor.
     *
     * @param int    $id
     * @param string $email
     * @param array  $rows
     * @param bool   $owner
     * @param bool   $editable
     */
    public function __construct($id, $email, array $rows, $owner, $editable)
    {
        $this->id       = $id;
        $this->email    = $email;
        $this->rows     = $rows;
        $this->owner    = $owner;
        $this->editable = $editable;
    }
}
