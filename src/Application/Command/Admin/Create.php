<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Admin;

class Create
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $role;

    /**
     * @var array
     */
    public $events;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var \DateTimeInterface
     */
    public $date;

    /**
     * @param string             $locale
     * @param \DateTimeInterface $date
     */
    public function __construct($locale, \DateTimeInterface $date)
    {
        $this->locale   = $locale;
        $this->password = substr(md5(uniqid()), 0, 8);
        $this->date     = $date;
    }
}
