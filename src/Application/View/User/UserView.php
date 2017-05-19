<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\User;

class UserView
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
    public $email;

    /**
     * UserView constructor.
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

    /**
     * @return string
     */
    public function getFullname()
    {
        return ucfirst(strtolower($this->firstname)) . ' ' . strtoupper($this->lastname);
    }
}
