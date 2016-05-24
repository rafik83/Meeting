<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant;

class CardView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    public $editable;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $position;

    /**
     * @var string
     */
    public $avatar;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @param int    $id
     * @param bool    $editable
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $avatar
     * @param bool   $owner
     */
    public function __construct($id, $editable, $firstname, $lastname, $position, $avatar, $owner)
    {
        $this->id        = $id;
        $this->editable  = $editable;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->position  = $position;
        $this->avatar    = $avatar;
        $this->owner     = $owner;
    }
}
