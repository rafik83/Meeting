<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class SheetParticipantView
{
    /**
     * "Nom"
     *
     * @var string
     */
    public $firstname;

    /**
     * "Prénom"
     *
     * @var string
     */
    public $lastname;

    /**
     * "Email"
     *
     * @var string
     */
    public $email;

    /**
     * SheetParticipantView constructor.
     *
     * @param string $firstname
     * @param string $lastname
     * @param string $email
     */
    public function __construct($firstname, $lastname, $email)
    {
        $this->firstname = $firstname;
        $this->lastname  = $lastname;
        $this->email     = $email;
    }
}
