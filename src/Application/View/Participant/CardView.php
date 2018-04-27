<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     * @var int
     */
    public $sheetId;

    /**
     * @var string
     */
    public $initials;

    /**
     * @param int    $id
     * @param bool   $editable
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $avatar
     * @param bool   $owner
     * @param int    $sheetId
     */
    public function __construct($id, $editable, $firstname, $lastname, $position, $avatar, $owner, $sheetId)
    {
        $this->id        = $id;
        $this->editable  = $editable;
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->position  = $position;
        $this->avatar    = $avatar;
        $this->owner     = $owner;
        $this->sheetId   = $sheetId;
        $this->initials = sprintf(
            '%s%s',
            strtoupper(mb_substr($firstname, 0, 1)),
            strtoupper(mb_substr($lastname, 0, 1))
        );
    }
}
