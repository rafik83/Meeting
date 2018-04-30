<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Meeting;

class MeetingParticipantView
{
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
    public $phone;

    /**
     * @var string
     */
    public $gender;

    /**
     * @var string
     */
    public $email;

    /**
     * MeetingParticipantView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $position
     * @param string $phone
     * @param string $gender
     * @param        $email
     */
    public function __construct($firstname, $lastname, $position, $phone, $gender, $email)
    {
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->position  = $position;
        $this->phone     = $phone;
        $this->gender    = $gender;
        $this->email     = $email;
    }
}
