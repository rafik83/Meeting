<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

/**
 * "Compte utilisateur".
 */
class User extends AbstractUser
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @param string $email
     * @param string $salt
     * @param string $password
     * @param string $locale
     */
    public function __construct($email, $salt, $password, $locale)
    {
        parent::__construct($email, $salt, $password);

        $this->locale   = $locale;
    }

    /**
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
