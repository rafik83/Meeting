<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Participant;

class ParticipantInfoView
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
    public $participantType;

    /**
     * ParticipantMailView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $participantType
     */
    public function __construct($firstname, $lastname, $participantType = null)
    {
        $this->firstname       = $firstname;
        $this->lastname        = $lastname;
        $this->participantType = $participantType;
    }
}
